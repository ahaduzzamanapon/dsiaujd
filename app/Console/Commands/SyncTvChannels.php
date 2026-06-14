<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncTvChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:tv-channels {--review : Send failed links to review queue instead of skipping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all TV channel sources sequentially (CricHD API, BDIX, Redforce, Static, and configured M3U playlists)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==========================================');
        $this->info('   TV Channels All-in-One Sync Started    ');
        $this->info('==========================================');
        $this->info('Started at: ' . now()->toDateTimeString());
        $this->info('');

        $withReview = $this->option('review');
        $args = $withReview ? ['--review' => true] : [];

        $scrapers = [
            [
                'label'   => 'CricHD API Scraper',
                'command' => 'sync:crichd-api',
                'args'    => $args,
            ],
            [
                'label'   => 'BDIX 198 Scraper',
                'command' => 'bdix198:sync',
                'args'    => $args,
            ],
            [
                'label'   => 'BDIXTV24 Scraper',
                'command' => 'bdixtv24:sync',
                'args'    => $args,
            ],
            [
                'label'   => 'RedForce Scraper',
                'command' => 'redforce:sync',
                'args'    => $args,
            ],
            [
                'label'   => 'Static Channels Scraper',
                'command' => 'sync:static-channels',
                'args'    => $args,
            ],
        ];

        // 1. Run Scrapers
        foreach ($scrapers as $scraper) {
            $this->info('──────────────────────────────────────────');
            $this->info("▶ Running Scraper: {$scraper['label']}");
            $this->info('──────────────────────────────────────────');

            try {
                $exitCode = Artisan::call($scraper['command'], $scraper['args']);
                $output = Artisan::output();
                $this->line(trim($output));

                if ($exitCode === 0) {
                    $this->info("✓ {$scraper['label']} completed successfully.");
                } else {
                    $this->warn("⚠ {$scraper['label']} finished with exit code {$exitCode}.");
                }
            } catch (\Exception $e) {
                $this->error("✗ {$scraper['label']} failed: " . $e->getMessage());
            }
            $this->line('');
        }

        // 2. Run M3U Playlists
        $m3uUrls = [
            'Roar Zone Playlist'   => 'https://raw.githubusercontent.com/abusaeeidx/BDxTV/refs/heads/main/roar-zone-playlist.m3u',
            'CricHD Playlist'      => 'https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/CricHD.m3u',
            'BD Playlist'          => 'https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/BD.m3u',
            'BDIX IPTV Playlist'   => 'https://raw.githubusercontent.com/abusaeeidx/Mrgify-BDIX-IPTV/refs/heads/main/playlist.m3u',
        ];

        foreach ($m3uUrls as $name => $url) {
            $this->info('──────────────────────────────────────────');
            $this->info("▶ Syncing M3U: {$name}");
            $this->info("Source: {$url}");
            $this->info('──────────────────────────────────────────');

            try {
                $exitCode = Artisan::call('m3u:sync', array_merge(['url' => $url], $args));
                $output = Artisan::output();
                $this->line(trim($output));

                if ($exitCode === 0) {
                    $this->info("✓ M3U {$name} synced successfully.");
                } else {
                    $this->warn("⚠ M3U {$name} finished with exit code {$exitCode}.");
                }
            } catch (\Exception $e) {
                $this->error("✗ M3U {$name} failed: " . $e->getMessage());
            }
            $this->line('');
        }

        $this->info('==========================================');
        $this->info('   TV Channels All-in-One Sync Finished   ');
        $this->info('==========================================');
        $this->info('Finished at: ' . now()->toDateTimeString());

        return 0;
    }
}
