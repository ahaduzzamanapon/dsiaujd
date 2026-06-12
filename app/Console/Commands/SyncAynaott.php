<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncAynaott extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aynaott:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live channels from AynaOTT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channelsUrl = 'https://cloudtv.akamaized.net/AynaOTT/BDcontent/channels/bundles/652fcf82a2649538da6fc6e3_bundle.json';
        $this->info("Fetching AynaOTT channels from: {$channelsUrl}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->get($channelsUrl);

            if (!$response->successful()) {
                $this->error("Failed to load channels bundle. HTTP Status: " . $response->status());
                return 1;
            }

            $json = $response->json();
            $data = $json['data'][0] ?? null;

            if (!$data || !isset($data['categories'])) {
                $this->error("Invalid channel bundle JSON format.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("Error downloading channels: " . $e->getMessage());
            return 1;
        }

        $imported = 0;

        foreach ($data['categories'] as $categoryData) {
            // Check if 'ALL' is in category details to extract all channels
            if (!str_contains(json_encode($categoryData), 'ALL')) {
                continue;
            }

            $channels = $categoryData['channels'] ?? [];
            $this->info("Found " . count($channels) . " channels in the ALL category.");

            foreach ($channels as $channel) {
                $channelId = $channel['_id'];
                $name = trim($channel['name']);
                
                // Construct logo URL
                $logoPath = $channel['images']['square'] ?? '';
                $logoUrl = 'https://cloudtv.akamaized.net/' . ltrim($logoPath, '/');
                
                $this->info("Syncing channel [{$channelId}]: {$name}...");

                $this->syncChannel($name, $logoUrl, $channelId);
                $imported++;
            }
        }

        $this->info("----------------------------------");
        $this->info("Sync completed. Successfully imported/updated {$imported} channels.");
        return 0;
    }

    /**
     * Import or update channel & server.
     */
    private function syncChannel(string $name, ?string $logo, string $aynaottId)
    {
        \App\Services\StreamDeduplicator::syncChannelWithDeduplication(
            $name,
            $logo,
            'AynaOTT',
            '/api/streams/aynaott/' . $aynaottId,
            'https://cloudtv.akamaized.net/',
            'https://cloudtv.akamaized.net',
            'Live Channel'
        );
    }
}
