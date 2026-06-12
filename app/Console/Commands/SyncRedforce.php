<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class SyncRedforce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redforce:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live channels from RedForce.live';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $homepageUrl = 'http://redforce.live/';
        $this->info("Fetching RedForce channels from: {$homepageUrl}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => 'http://redforce.live/'
            ])->get($homepageUrl);

            if (!$response->successful()) {
                $this->error("Failed to load RedForce homepage. HTTP Status: " . $response->status());
                return 1;
            }

            $html = $response->body();

        } catch (\Exception $e) {
            $this->error("Error downloading RedForce homepage: " . $e->getMessage());
            return 1;
        }

        // Regex pattern to extract classes, stream ID, logo path, and alt text (channel name)
        $pattern = '/<li class="([^"]+)">\s*<a[^>]+onclick="[^"]*player\.php\?stream=(\d+)[^"]*"[^>]*>\s*<img[^>]+src="([^"]+)"[^>]+alt="([^"]+)"/s';

        if (!preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            $this->error("No channels matched the scraping regex pattern.");
            return 1;
        }

        $imported = 0;
        $this->info("Found " . count($matches) . " channels on RedForce.live.");

        foreach ($matches as $match) {
            // Extract category from classes (e.g. "Sports All" -> "Sports")
            $classes = explode(' ', $match[1]);
            $categoryName = trim($classes[0] !== 'All' ? $classes[0] : ($classes[1] ?? 'Live Channel'));
            if (empty($categoryName) || strtolower($categoryName) === 'all') {
                $categoryName = 'Live Channel';
            }

            $streamId = $match[2];
            
            // Construct absolute logo URL
            $logoPath = ltrim($match[3], '/');
            $logoUrl = 'http://redforce.live/' . $logoPath;

            $name = html_entity_decode(trim($match[4]));

            $this->info("Syncing channel [{$streamId}]: {$name} (Category: {$categoryName})...");
            
            $this->syncChannel($name, $logoUrl, $streamId, $categoryName);
            $imported++;
        }

        $this->info("----------------------------------");
        $this->info("Sync completed. Successfully imported/updated {$imported} channels.");
        return 0;
    }

    /**
     * Import or update channel & server.
     */
    private function syncChannel(string $name, ?string $logo, string $redforceId, string $categoryName)
    {
        \App\Services\StreamDeduplicator::syncChannelWithDeduplication(
            $name,
            $logo,
            'RedForce',
            '/api/streams/redforce/' . $redforceId,
            'http://redforce.live/',
            'http://redforce.live',
            $categoryName
        );
    }
}
