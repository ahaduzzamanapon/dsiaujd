<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncCricHdApiJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:crichd-api {url? : The URL of the CricHD API JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync active CricHD streams from JSON API and associate them with standard categories and the Fresh category';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?? 'https://raw.githubusercontent.com/abusaeeidx/CricHd-playlists-Auto-Update-permanent/refs/heads/main/api.json';
        
        $this->info("Fetching CricHD API JSON from: {$url}");
        
        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                $this->error("Failed to download JSON. HTTP status: " . $response->status());
                return 1;
            }
            $channelsData = $response->json();
            if (empty($channelsData) || !is_array($channelsData)) {
                $this->error("Failed to parse JSON content or empty array returned.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error downloading CricHD API JSON: " . $e->getMessage());
            return 1;
        }

        $total = 0;
        $imported = 0;
        $skipped_offline = 0;
        $skipped_duplicate = 0;

        // Ensure the Fresh category exists in DB
        $freshCategory = Category::firstOrCreate(
            ['name' => 'Fresh'],
            ['order' => 100]
        );

        foreach ($channelsData as $channel) {
            $name = trim($channel['name'] ?? '');
            $srvUrl = trim($channel['link'] ?? '');
            $channelId = trim($channel['id'] ?? '');
            
            if (empty($name) || empty($srvUrl)) {
                continue;
            }

            $total++;
            $logo = $channel['logo'] ?? null;
            $referer = $channel['referer'] ?? null;
            $origin = $channel['origin'] ?? null;

            $this->info("Processing [{$total}]: {$name}");

            // 1. Check if this exact URL already exists (no change needed)
            $urlExists = StreamServer::where('url', $srvUrl)->exists();
            if ($urlExists) {
                $skipped_duplicate++;
                $this->line("  -> Already up-to-date. Skipped.");
                continue;
            }

            // 2. Check if we have an older URL for the same channel (expired signed URL)
            //    Match by channel id pattern in the URL path (e.g. /hls/ptvpk.m3u8)
            $oldServer = null;
            if (!empty($channelId)) {
                $oldServer = StreamServer::where('url', 'like', "%/hls/{$channelId}.m3u8%")
                    ->first();
            }

            if ($oldServer) {
                // 2a. Validate the new URL before replacing the old one
                if (!$this->checkLink($srvUrl, $referer, $origin)) {
                    $skipped_offline++;
                    $this->warn("  -> New URL is also down. Keeping old entry.");
                    continue;
                }

                // 2b. Update the URL in place (preserving the stream/server relationship)
                $oldServer->update([
                    'url' => $srvUrl,
                    'http_referer' => $referer,
                    'http_origin' => $origin,
                ]);
                $imported++;
                $this->info("  -> URL refreshed (old token replaced)!");
                continue;
            }

            // 3. Validate link is online before creating new entry
            if (!$this->checkLink($srvUrl, $referer, $origin)) {
                $skipped_offline++;
                $this->warn("  -> Link is down. Skipped.");
                continue;
            }

            try {
                // Determine category (default to Sports since it's a sports source)
                $suggestedCategory = 'Sports';

                // Sync the channel and append the server using StreamDeduplicator
                $stream = \App\Services\StreamDeduplicator::syncChannelWithDeduplication(
                    $name,
                    $logo,
                    'Server',
                    $srvUrl,
                    $referer,
                    $origin,
                    $suggestedCategory
                );

                // Associate the stream with the Fresh category tab
                $stream->categories()->syncWithoutDetaching([$freshCategory->id]);

                $imported++;
                $this->info("  -> Synced successfully!");
            } catch (\Exception $e) {
                $this->error("  -> Failed to sync: " . $e->getMessage());
            }
        }

        $this->info("----------------------------------");
        $this->info("Sync completed.");
        $this->info("Total channels parsed: {$total}");
        $this->info("Successfully synced: {$imported}");
        $this->info("Skipped (duplicate URL): {$skipped_duplicate}");
        $this->info("Skipped (offline link): {$skipped_offline}");

        return 0;
    }

    /**
     * Check if a stream URL is active and responding.
     */
    private function checkLink(string $url, ?string $referer = null, ?string $origin = null): bool
    {
        $resolved = \App\Models\StreamServer::resolveHeadersForUrl($url, $referer, $origin);
        $referer = $resolved['referer'];
        $origin = $resolved['origin'];

        $this->info("  Checking link: {$url}");
        if ($referer) $this->info("    Referer: {$referer}");
        if ($origin) $this->info("    Origin: {$origin}");

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6); // 6 seconds timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4); // 4 seconds connection timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $headers = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ];

            if ($referer) {
                curl_setopt($ch, CURLOPT_REFERER, $referer);
            }
            if ($origin) {
                $headers[] = "Origin: {$origin}";
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $res = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            // If status is 0 (connection failure), retry once after a short delay
            if ($statusCode === 0) {
                sleep(2);
                $ch2 = curl_init($url);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch2, CURLOPT_MAXREDIRS, 3);
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
                if ($referer) curl_setopt($ch2, CURLOPT_REFERER, $referer);
                curl_exec($ch2);
                $statusCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                $err = curl_error($ch2);
                curl_close($ch2);
                $this->warn("    [Retry] Status Code: {$statusCode}");
            } else {
                $this->info("    Status Code: {$statusCode}");
            }

            if ($err) {
                $this->error("    Curl Error: {$err}");
            }

            // 200-399 success/redirect status codes or 403 (for token/auth restricted pages)
            return ($statusCode >= 200 && $statusCode < 400) || $statusCode === 403;
        } catch (\Exception $e) {
            $this->error("    Exception: " . $e->getMessage());
            return false;
        }
    }
}
