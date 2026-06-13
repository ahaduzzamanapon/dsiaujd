<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncStaticChannelsJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:static-channels {url? : The URL of the static channels JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync active (online) streams from a static channels JSON repository and map them to the Fresh category';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?? 'https://raw.githubusercontent.com/time2shine/IPTV/refs/heads/master/static_channels.json';
        
        $this->info("Fetching static channels JSON from: {$url}");
        
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
            $this->error("Error downloading static channels JSON: " . $e->getMessage());
            return 1;
        }

        $total = 0;
        $imported = 0;
        $skipped_offline = 0;

        // Ensure the Fresh category exists in DB with a high order value so it appears first
        $freshCategory = Category::firstOrCreate(
            ['name' => 'Fresh'],
            ['order' => 100]
        );
        // Make sure its order is set to 100 if it was already created earlier with a default order
        if ($freshCategory->order < 100) {
            $freshCategory->update(['order' => 100]);
        }

        foreach ($channelsData as $channelName => $channelInfo) {
            if (empty($channelInfo['links']) || !is_array($channelInfo['links'])) {
                continue;
            }

            // Find all online/active links for this channel
            $onlineLinks = array_filter($channelInfo['links'], function ($link) {
                return isset($link['status']) && $link['status'] === 'online' && !empty($link['url']);
            });

            if (empty($onlineLinks)) {
                $skipped_offline++;
                continue;
            }

            $total++;
            $logo = $channelInfo['tvg_logo'] ?? null;
            $group = $channelInfo['group'] ?? 'Live Channel';

            $this->info("Processing [{$total}]: {$channelName}");

            foreach ($onlineLinks as $index => $link) {
                $srvUrl = trim($link['url']);
                $srvName = count($onlineLinks) > 1 ? 'Server ' . ($index + 1) : 'Server';

                try {
                    // Sync the channel and append the server using StreamDeduplicator
                    $stream = \App\Services\StreamDeduplicator::syncChannelWithDeduplication(
                        $channelName,
                        $logo,
                        $srvName,
                        $srvUrl,
                        null,
                        null,
                        $group
                    );

                    // Associate the stream with the Fresh category tab
                    $stream->categories()->syncWithoutDetaching([$freshCategory->id]);

                    $imported++;
                    $this->info("  -> Synced '{$srvName}' successfully!");
                } catch (\Exception $e) {
                    $this->error("  -> Failed to sync server: " . $e->getMessage());
                }
            }
        }

        $this->info("----------------------------------");
        $this->info("Sync completed.");
        $this->info("Total channels with online links: {$total}");
        $this->info("Successfully synced/imported servers: {$imported}");
        $this->info("Channels skipped (no online links): {$skipped_offline}");

        return 0;
    }
}
