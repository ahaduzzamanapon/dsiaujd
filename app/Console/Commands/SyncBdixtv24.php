<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncBdixtv24 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdixtv24:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live channels and all streaming servers from BDIXTV24.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://bdixtv24.com/';
        $this->info("Fetching BDIXTV24 homepage from: {$url}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->get($url);

            if (!$response->successful()) {
                $this->error("Failed to load homepage. HTTP Status: " . $response->status());
                return 1;
            }
            $html = $response->body();
        } catch (\Exception $e) {
            $this->error("Error downloading homepage: " . $e->getMessage());
            return 1;
        }

        // Match all channels inside bdixtv24.com homepage
        $pattern = '/<article[^>]*id="post-(\d+)"[^>]*>.*?<img[^>]*src="([^"]+)"[^>]*alt="([^"]+)"[^>]*>.*?<a[^>]*href="(https:\/\/bdixtv24\.com\/live-tv\/[^"]+\/)"/is';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $totalChannels = count($matches);
        $this->info("Found {$totalChannels} potential channels on the homepage.");

        $imported = 0;

        foreach ($matches as $channel) {
            $postId = $channel[1];
            $logoUrl = $channel[2];
            $title = html_entity_decode($channel[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $detailLink = $channel[4];

            $this->info("Scraping channel [{$postId}]: {$title}...");

            try {
                $detailResponse = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->get($detailLink);

                if (!$detailResponse->successful()) {
                    $this->warn("  -> Failed to load channel page: {$detailLink}");
                    continue;
                }
                $detailHtml = $detailResponse->body();
            } catch (\Exception $e) {
                $this->warn("  -> Error loading channel page: " . $e->getMessage());
                continue;
            }

            // Find all server tabs in the detail page
            $serverPattern = '/<li[^>]*class=["\'][^"\']*dooplay_player_option[^"\']*["\'][^>]*data-type=["\']([^"\']+)["\'][^>]*data-post=["\']([^"\']+)["\'][^>]*data-nume=["\']([^"\']+)["\'][^>]*>.*?<span[^>]*class=["\']title["\'][^>]*>([^<]+)<\/span>/is';
            preg_match_all($serverPattern, $detailHtml, $serversMatched, PREG_SET_ORDER);

            $servers = [];
            foreach ($serversMatched as $srvMatch) {
                $type = $srvMatch[1];
                $post = $srvMatch[2];
                $nume = $srvMatch[3];
                $serverName = trim($srvMatch[4]);

                // Call AJAX to get embed code
                try {
                    $ajaxResponse = Http::asForm()->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Referer' => $detailLink
                    ])->post('https://bdixtv24.com/wp-admin/admin-ajax.php', [
                        'action' => 'doo_player_ajax',
                        'post' => $post,
                        'nume' => $nume,
                        'type' => $type
                    ]);

                    if ($ajaxResponse->successful()) {
                        $json = $ajaxResponse->json();
                        $embedUrl = $json['embed_url'] ?? '';

                        // Extract stream URL
                        $streamUrl = null;
                        if (preg_match('/source\s*:\s*["\'](https?:\/\/[^"\']+)["\']/i', $embedUrl, $uMatch)) {
                            $streamUrl = stripslashes($uMatch[1]);
                        } elseif (preg_match('/src\s*=\s*["\'](https?:\/\/[^"\']+)["\']/i', $embedUrl, $uMatch)) {
                            $streamUrl = stripslashes($uMatch[1]);
                        } elseif (preg_match('/(https?:\/\/[^"\'\s<>]+?\.(?:m3u8|mp4|m3u))/i', $embedUrl, $uMatch)) {
                            $streamUrl = stripslashes($uMatch[1]);
                        }

                        if ($streamUrl) {
                            $servers[] = [
                                'name' => $serverName,
                                'url' => $streamUrl
                            ];
                            $this->line("  -> Found {$serverName}: {$streamUrl}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("  -> Error calling AJAX for {$serverName}: " . $e->getMessage());
                }
            }

            if (!empty($servers)) {
                $this->syncChannel($title, $logoUrl, $servers);
                $imported++;
            } else {
                $this->warn("  -> No stream servers found.");
            }
        }

        $this->info("----------------------------------");
        $this->info("Sync completed. Successfully imported/updated {$imported} channels.");
        return 0;
    }

    /**
     * Import or update channel & servers.
     */
    private function syncChannel(string $name, ?string $logo, array $servers)
    {
        $stream = Stream::where('name', $name)->first();

        $isSports = false;
        $nameLower = strtolower($name);
        if (
            str_contains($nameLower, 'sport') ||
            str_contains($nameLower, 'cricket') ||
            str_contains($nameLower, 'football') ||
            str_contains($nameLower, 'willow') ||
            str_contains($nameLower, 'espn') ||
            str_contains($nameLower, 'fifa') ||
            str_contains($nameLower, 'premier league')
        ) {
            $isSports = true;
        }

        if (!$stream) {
            $stream = Stream::create([
                'name' => $name,
                'logo' => $logo ?: null,
                'sport_type' => 'other',
                'is_permanent' => true,
                'show_in_events' => false,
                'show_in_sports' => $isSports,
                'show_in_tv' => true,
                'is_active' => true,
            ]);

            $category = Category::firstOrCreate(['name' => 'Live Channel']);
            $stream->categories()->syncWithoutDetaching([$category->id]);
        }

        $existingServersCount = $stream->servers()->count();
        foreach ($servers as $idx => $srv) {
            $srvUrl = $srv['url'];
            $srvName = $srv['name'] ?: ('Server ' . ($existingServersCount + $idx + 1));

            // Check if server with this URL already exists for this stream
            $serverExists = StreamServer::where('stream_id', $stream->id)->where('url', $srvUrl)->exists();
            if (!$serverExists) {
                StreamServer::create([
                    'stream_id' => $stream->id,
                    'name' => $srvName,
                    'stream_type' => 'm3u8',
                    'url' => $srvUrl,
                    'http_referer' => 'https://bdixtv24.com/',
                    'http_origin' => 'https://bdixtv24.com',
                    'order' => $existingServersCount + $idx,
                ]);
            }
        }
    }
}
