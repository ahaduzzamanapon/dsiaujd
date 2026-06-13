<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\StreamDeduplicator;

class SyncBdix198 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdix198:sync {--review : Send failed links to review queue instead of skipping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live channels from BDIX 198.195.239.50 portal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonUrl = 'http://198.195.239.50/tv_channels.json';
        $this->info("Fetching BDIX 198 channels from: {$jsonUrl}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->get($jsonUrl);

            if (!$response->successful()) {
                $this->error("Failed to load BDIX 198 channels JSON. HTTP Status: " . $response->status());
                return 1;
            }

            $data = $response->json();
            $channels = $data['channels'] ?? [];

        } catch (\Exception $e) {
            $this->error("Error downloading BDIX 198 channels: " . $e->getMessage());
            return 1;
        }

        $imported = 0;
        $this->info("Found " . count($channels) . " channels in portal list.");

        foreach ($channels as $ch) {
            // Skip hidden channels to match web portal display
            if (isset($ch['status']) && $ch['status'] === 'hidden') {
                continue;
            }

            $name = trim($ch['name']);
            $url = $ch['url'];
            
            // Construct absolute logo URL
            $logoPath = $ch['logo'] ?? '';
            $logoUrl = '';
            if (!empty($logoPath)) {
                if (str_starts_with($logoPath, 'http')) {
                    $logoUrl = $logoPath;
                } else {
                    $logoUrl = 'http://198.195.239.50/' . ltrim($logoPath, '/');
                }
            }

            $categoryName = $ch['category'] ?? 'Live Channel';

            $this->info("Syncing BDIX 198 channel: {$name} (Category: {$categoryName})...");

            StreamDeduplicator::syncChannelWithDeduplication(
                $name,
                $logoUrl ?: null,
                'BDIX 198',
                $url,
                'http://198.195.239.50/',
                'http://198.195.239.50',
                $categoryName
            );

            $imported++;
        }

        $this->info("----------------------------------");
        $this->info("Sync completed. Successfully imported/updated {$imported} channels.");
        return 0;
    }
}
