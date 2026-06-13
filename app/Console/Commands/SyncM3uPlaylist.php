<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;
use App\Models\PendingStream;

class SyncM3uPlaylist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'm3u:sync {url? : The URL of the M3U playlist} {--review : Send failed links to review queue instead of skipping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync streams from an external M3U playlist, validating links and custom headers before importing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?? 'https://raw.githubusercontent.com/abusaeeidx/BDxTV/refs/heads/main/roar-zone-playlist.m3u';
        
        $this->info("Fetching M3U playlist from: {$url}");
        
        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                $this->error("Failed to download playlist. HTTP status: " . $response->status());
                return 1;
            }
            $m3uContent = $response->body();
        } catch (\Exception $e) {
            $this->error("Error downloading M3U: " . $e->getMessage());
            return 1;
        }

        $lines = explode("\n", $m3uContent);
        $total = 0;
        $imported = 0;
        $skipped_duplicate = 0;
        $skipped_offline = 0;

        $currentStream = null;
        $vlcOptions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $logo = '';
                $groupTitle = 'Other';
                $streamName = 'Unnamed Stream';

                if (preg_match('/tvg-logo="([^"]+)"/i', $line, $match)) {
                    $logo = $match[1];
                }
                
                if (preg_match('/group-title="([^"]+)"/i', $line, $match)) {
                    $groupTitle = $match[1];
                }
                
                $parts = explode(',', $line, 2);
                if (count($parts) > 1) {
                    $streamName = trim($parts[1]);
                }

                $currentStream = [
                    'name' => $streamName,
                    'logo' => $logo,
                    'group_title' => $groupTitle,
                ];
            } elseif (str_starts_with($line, '#EXTVLCOPT:')) {
                $opt = substr($line, 11);
                $parts = explode('=', $opt, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $val = trim($parts[1]);
                    $vlcOptions[$key] = $val;
                }
            } elseif (str_starts_with($line, 'http')) {
                if ($currentStream) {
                    $currentStream['url'] = $line;
                    $currentStream['referer'] = $vlcOptions['http-referrer'] ?? $vlcOptions['referrer'] ?? null;
                    $currentStream['origin'] = $vlcOptions['http-origin'] ?? $vlcOptions['origin'] ?? null;
                    $total++;
                    
                    $this->info("Processing [{$total}]: {$currentStream['name']}");
                    
                    $result = $this->syncStream($currentStream);
                    
                    if ($result === 'duplicate') {
                        $skipped_duplicate++;
                        $this->line("  -> Already exists. Skipped.");
                    } elseif ($result === 'offline') {
                        $skipped_offline++;
                        $this->warn("  -> Link is down. Skipped.");
                    } elseif ($result === 'review') {
                        $skipped_offline++;
                        $this->warn("  -> Link failed. Saved to Review Queue.");
                    } elseif ($result === 'imported') {
                        $imported++;
                        $this->info("  -> Sync successful!");
                    }
                    
                    $currentStream = null;
                    $vlcOptions = []; // reset options after processing URL
                }
            }
        }

        $this->info("----------------------------------");
        $this->info("Sync completed.");
        $this->info("Total parsed: {$total}");
        $this->info("Successfully imported/updated: {$imported}");
        $this->info("Skipped (duplicate URL): {$skipped_duplicate}");
        $this->info("Skipped (offline link): {$skipped_offline}");

        return 0;
    }

    /**
     * Process and sync a single stream.
     */
    private function syncStream(array $streamData): string
    {
        $url = trim($streamData['url']);
        $referer = $streamData['referer'] ?? null;
        $origin = $streamData['origin'] ?? null;
        
        // 1. Rule: If the same streaming URL already exists in stream_servers, do nothing.
        $urlExists = StreamServer::where('url', $url)->exists();
        if ($urlExists) {
            return 'duplicate';
        }

        // 2. Before importing, verify that the stream link works using custom headers if present.
        if (!$this->checkLink($url, $referer, $origin)) {
            if ($this->option('review')) {
                $groupTitle = trim($streamData['group_title'] ?? 'Other');
                PendingStream::create([
                    'name'         => $streamData['name'],
                    'logo'         => $streamData['logo'] ?? null,
                    'url'          => $url,
                    'http_referer' => $referer,
                    'http_origin'  => $origin,
                    'category'     => ucfirst(strtolower($groupTitle)),
                    'source'       => 'M3U Playlist',
                    'reason'       => 'failed_check',
                ]);
                return 'review';
            }
            return 'offline';
        }

        // Parse category and sport configurations
        $groupTitleName = trim($streamData['group_title'] ?? 'Other');
        $categoryName = ucfirst(strtolower($groupTitleName));
        
        // Decide if it should go to Sports tab
        $isSports = false;
        $nameLower = strtolower($streamData['name']);
        if (
            strtolower($groupTitleName) === 'sports' || 
            str_contains($nameLower, 'sport') || 
            str_contains($nameLower, 'cricket') || 
            str_contains($nameLower, 'football') ||
            str_contains($nameLower, 'willow') ||
            str_contains($nameLower, 'espn')
        ) {
            $isSports = true;
        }

        // 3. Rule: Use StreamDeduplicator to find/create stream and link server
        \App\Services\StreamDeduplicator::syncChannelWithDeduplication(
            $streamData['name'],
            $streamData['logo'],
            'Server',
            $url,
            $referer,
            $origin,
            $categoryName
        );

        return 'imported';
    }

    /**
     * Check if a stream URL is active and responding.
     */
    private function checkLink(string $url, ?string $referer = null, ?string $origin = null): bool
    {
        $resolved = \App\Models\StreamServer::resolveHeadersForUrl($url, $referer, $origin);
        $referer = $resolved['referer'];
        $origin = $resolved['origin'];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 seconds connection timeout
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
            
            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Retry once on connection failure (status 0)
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
                curl_close($ch2);
            }

            // 200-399 success/redirect status codes or 403 (for token/auth restricted pages)
            return ($statusCode >= 200 && $statusCode < 400) || $statusCode === 403;
        } catch (\Exception $e) {
            return false;
        }
    }
}
