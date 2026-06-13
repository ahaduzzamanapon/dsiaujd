<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncFancodeEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fancode:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch live and upcoming matches from the Fancode events JSON repository and import them as Live Events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://raw.githubusercontent.com/kajju027/Fancode-Events-Json/main/fancode.json';
        $this->info("Fetching Fancode events from: {$url}");

        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                $this->error("Failed to fetch Fancode JSON. HTTP Status: " . $response->status());
                return 1;
            }
            $data = $response->json();
        } catch (\Exception $e) {
            $this->error("Error fetching Fancode events: " . $e->getMessage());
            return 1;
        }

        if (!isset($data['matches']) || !is_array($data['matches'])) {
            $this->error("Invalid Fancode JSON structure: 'matches' key not found.");
            return 1;
        }

        $matches = $data['matches'];
        $imported = 0;
        $total = count($matches);

        // Retrieve headers from JSON if available, otherwise default
        $referer = $data['headers']['Referer'] ?? 'https://fancode.com/';
        $origin = 'https://fancode.com';

        foreach ($matches as $match) {
            $title = $match['title'] ?? 'Unknown Match';
            $status = strtoupper($match['status'] ?? 'UPCOMING');

            // Only sync active/upcoming matches
            if ($status !== 'LIVE' && $status !== 'UPCOMING' && $status !== 'STARTED' && $status !== 'NOT_STARTED') {
                continue;
            }

            $this->info("Processing match: {$title}");

            // Extract stream links
            $servers = [];
            
            // 1. Primary stream URL
            if (!empty($match['streams']['primary'])) {
                $servers[] = [
                    'name' => 'Fancode CDN 1',
                    'url' => trim($match['streams']['primary']),
                ];
            }

            // 2. Backup stream URLs
            if (isset($match['streams']['backup']) && is_array($match['streams']['backup'])) {
                $backupIndex = 2;
                foreach ($match['streams']['backup'] as $key => $backupUrl) {
                    if (!empty($backupUrl) && filter_var($backupUrl, FILTER_VALIDATE_URL)) {
                        $name = 'Fancode CDN ' . $backupIndex;
                        if (str_contains(strtolower($key), 'dai')) {
                            $name = 'DAI CDN Backup';
                        }
                        $servers[] = [
                            'name' => $name,
                            'url' => trim($backupUrl),
                        ];
                        $backupIndex++;
                    }
                }
            }

            // Fallback to match DAI URL/adfree URL if structure is flat (as in user's pasted json)
            if (empty($servers)) {
                if (!empty($match['dai_url'])) {
                    $servers[] = [
                        'name' => 'DAI CDN',
                        'url' => trim($match['dai_url']),
                    ];
                }
                if (!empty($match['adfree_url'])) {
                    $servers[] = [
                        'name' => 'Adfree CDN',
                        'url' => trim($match['adfree_url']),
                    ];
                }
            }

            // Validate stream links if any are present
            $verifiedServers = [];
            foreach ($servers as $srv) {
                if ($this->checkLink($srv['url'], $referer, $origin)) {
                    $verifiedServers[] = $srv;
                }
            }

            // Parse match start time
            try {
                $startTimeStr = $match['startTime'] ?? now()->toDateTimeString();
                $startTime = Carbon::parse($startTimeStr);
            } catch (\Exception $e) {
                $startTime = now();
            }
            
            // Set match expiration to 5 hours after start
            $expireTime = (clone $startTime)->addHours(5);

            // Extract team details
            $team1Name = $match['team_1'] ?? null;
            $team2Name = $match['team_2'] ?? null;
            $team1Logo = null;
            $team2Logo = null;

            if (isset($match['teams']) && is_array($match['teams']) && count($match['teams']) >= 2) {
                $team1Name = $match['teams'][0]['name'] ?? $team1Name;
                $team1Logo = $match['teams'][0]['flag'] ?? null;
                $team2Name = $match['teams'][1]['name'] ?? $team2Name;
                $team2Logo = $match['teams'][1]['flag'] ?? null;
            }

            $sportType = strtolower($match['category'] ?? $match['event_category'] ?? 'cricket');

            // Find or create Stream
            $stream = Stream::updateOrCreate(
                ['name' => $title],
                [
                    'logo' => $match['image'] ?? $match['src'] ?? null,
                    'sport_type' => $sportType,
                    'team1_name' => $team1Name,
                    'team1_logo' => $team1Logo,
                    'team2_name' => $team2Name,
                    'team2_logo' => $team2Logo,
                    'is_permanent' => false,
                    'start_time' => $startTime,
                    'expire_time' => $expireTime,
                    'show_in_events' => true,
                    'show_in_sports' => true,
                    'show_in_tv' => false,
                    'is_active' => true,
                ]
            );

            // Re-sync Stream Servers
            // Clear existing servers to prevent stacking duplicate servers on updates
            $stream->servers()->delete();

            foreach ($verifiedServers as $index => $srv) {
                StreamServer::create([
                    'stream_id' => $stream->id,
                    'name' => $srv['name'],
                    'stream_type' => 'm3u8',
                    'url' => $srv['url'],
                    'http_referer' => $referer,
                    'http_origin' => $origin,
                    'order' => $index,
                ]);
            }

            $imported++;
            $this->info(" -> Match synced successfully!");
        }

        $this->info("----------------------------------");
        $this->info("Fancode sync completed. Total matches synced: {$imported}/{$total}");
        return 0;
    }

    /**
     * Check if a stream URL is active and responding (allows 403 Forbidden geoblock).
     */
    private function checkLink(string $url, ?string $referer = null, ?string $origin = null): bool
    {
        $resolved = \App\Models\StreamServer::resolveHeadersForUrl($url, $referer, $origin);
        $referer = $resolved['referer'];
        $origin = $resolved['origin'];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4); // 4 seconds timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 2 seconds connection timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
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

            // 200-399 success/redirect status codes or 403 (for token/auth restricted pages)
            return ($statusCode >= 200 && $statusCode < 400) || $statusCode === 403;
        } catch (\Exception $e) {
            return false;
        }
    }
}
