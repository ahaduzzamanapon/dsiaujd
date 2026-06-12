<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stream;
use App\Models\StreamServer;
use App\Services\StreamDeduplicator;

class CleanAndMergeStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:clean-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan existing database streams, identify duplicates, and merge them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Scanning Database for Duplicate Channels ===");

        $streams = Stream::with('servers', 'categories')->get();
        $this->info("Total streams found in database: " . $streams->count());

        $canonicalStreams = [];
        $mergedCount = 0;

        foreach ($streams as $stream) {
            /** @var \App\Models\Stream $stream */
            $normalizedName = StreamDeduplicator::normalizeName($stream->name);
            $matchedCanonical = null;

            foreach ($canonicalStreams as $canonical) {
                /** @var \App\Models\Stream $canonical */
                $normalizedCanonicalName = StreamDeduplicator::normalizeName($canonical->name);

                if ($normalizedCanonicalName === $normalizedName) {
                    $matchedCanonical = $canonical;
                    break;
                }

                // Check similarity
                similar_text($normalizedCanonicalName, $normalizedName, $percent);
                if ($percent >= 85) {
                    if (!StreamDeduplicator::hasConflictingNumbers($normalizedCanonicalName, $normalizedName)) {
                        $matchedCanonical = $canonical;
                        break;
                    }
                }
            }

            if ($matchedCanonical) {
                // We found a duplicate! Let's merge $stream into $matchedCanonical
                $this->info("Merging Duplicate [{$stream->id}] '{$stream->name}' into Canonical [{$matchedCanonical->id}] '{$matchedCanonical->name}'...");

                // 1. Move all servers from duplicate to canonical
                foreach ($stream->servers as $server) {
                    // Check if canonical already has a server with the same URL
                    $exists = StreamServer::where('stream_id', $matchedCanonical->id)
                        ->where('url', $server->url)
                        ->exists();

                    if (!$exists) {
                        $maxOrder = StreamServer::where('stream_id', $matchedCanonical->id)->max('order');
                        $server->update([
                            'stream_id' => $matchedCanonical->id,
                            'order' => is_null($maxOrder) ? 0 : ($maxOrder + 1),
                        ]);
                    } else {
                        // Delete duplicate server
                        $server->delete();
                    }
                }

                // 2. Merge categories
                $categoriesToSync = $stream->categories->pluck('id')->toArray();
                $matchedCanonical->categories()->syncWithoutDetaching($categoriesToSync);

                // 3. Delete the duplicate stream
                $stream->delete();
                $mergedCount++;
            } else {
                // Normalize and clean category of this stream while we are here
                $canonicalCategory = StreamDeduplicator::resolveCanonicalCategory($stream->name, $stream->categories->first()->name ?? '');
                $category = \App\Models\Category::firstOrCreate(['name' => $canonicalCategory]);
                $stream->categories()->syncWithoutDetaching([$category->id]);
                
                // Add to list of canonical streams for subsequent comparisons
                $canonicalStreams[] = $stream;
            }
        }

        $this->info("----------------------------------");
        $this->info("Scan completed. Merged {$mergedCount} duplicate channels.");
        return 0;
    }
}
