<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\StreamDeduplicator;

class SyncRedforce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redforce:sync {--html= : Path to a saved HTML file (use when server cannot reach BDIX network)}';

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
        $htmlFilePath = $this->option('html');

        if ($htmlFilePath) {
            // Use provided HTML file (for when server cannot reach BDIX network)
            if (!file_exists($htmlFilePath)) {
                $this->error("HTML file not found: {$htmlFilePath}");
                return 1;
            }
            $html = file_get_contents($htmlFilePath);
            $this->info("Using saved HTML from: {$htmlFilePath}");
        } else {
            // Try to fetch directly from RedForce.live
            $homepageUrl = 'http://redforce.live/';
            $this->info("Fetching RedForce channels from: {$homepageUrl}");
            $this->info("Note: RedForce.live is a BDIX-only site. If this times out, use --html option.");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer'    => 'http://redforce.live/'
                ])->timeout(10)->get($homepageUrl);

                if (!$response->successful()) {
                    $this->error("Failed to load RedForce homepage. HTTP Status: " . $response->status());
                    $this->warn("Tip: redforce.live is BDIX-only. Save the HTML file and use: php artisan redforce:sync --html=/path/to/redforce.html");
                    return 1;
                }

                $html = $response->body();

            } catch (\Exception $e) {
                $this->error("Error downloading RedForce homepage: " . $e->getMessage());
                $this->warn("Tip: redforce.live is BDIX-only. Save the HTML file and use: php artisan redforce:sync --html=/path/to/redforce.html");
                return 1;
            }
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

            StreamDeduplicator::syncChannelWithDeduplication(
                $name,
                $logoUrl,
                'RedForce',
                '/api/streams/redforce/' . $streamId,
                'http://redforce.live/',
                'http://redforce.live',
                $categoryName
            );

            $imported++;
        }

        $this->info("----------------------------------");
        $this->info("Sync completed. Successfully imported/updated {$imported} channels.");
        return 0;
    }
}
