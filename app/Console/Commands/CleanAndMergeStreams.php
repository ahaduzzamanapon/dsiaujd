<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Stream;
use App\Models\StreamServer;
use App\Services\StreamDeduplicator;

class CleanAndMergeStreams extends Command
{
    protected $signature = 'streams:clean-duplicates {--ai : Use Gemini AI for smarter matching (slower but more accurate)}';
    protected $description = 'Scan existing database streams, identify duplicates, and merge them';

    public function handle()
    {
        $useAi = $this->option('ai') || !empty(env('GEMINI_API_KEY'));

        $this->info("=== Scanning Database for Duplicate Channels ===");
        if ($useAi && !empty(env('GEMINI_API_KEY'))) {
            $this->info("Mode: Gemini AI-assisted deduplication");
        } else {
            $this->info("Mode: Local similarity matching (set GEMINI_API_KEY for AI mode)");
            $useAi = false;
        }

        $streams = Stream::with('servers', 'categories')->orderBy('id')->get();
        $this->info("Total streams found in database: " . $streams->count());

        // Build the list of canonical streams as we process
        $canonicalStreams = [];  // ['stream' => Stream, 'normalizedName' => string]
        $mergedCount = 0;
        $checkedCount = 0;

        foreach ($streams as $stream) {
            $checkedCount++;
            $normalizedNew = StreamDeduplicator::normalizeName($stream->name);
            $matchedCanonical = null;

            // --- Step 1: Exact normalized name match (fast, always run) ---
            foreach ($canonicalStreams as $entry) {
                if ($entry['normalizedName'] === $normalizedNew) {
                    $matchedCanonical = $entry['stream'];
                    break;
                }
            }

            // --- Step 2: Similarity match (local, always run as pre-filter) ---
            if (!$matchedCanonical) {
                foreach ($canonicalStreams as $entry) {
                    similar_text($entry['normalizedName'], $normalizedNew, $percent);
                    if ($percent >= 85) {
                        if (StreamDeduplicator::isKnownConflict($entry['stream']->name, $stream->name)) {
                            continue;
                        }
                        if (!StreamDeduplicator::hasConflictingNumbers($entry['normalizedName'], $normalizedNew)) {
                            $matchedCanonical = $entry['stream'];
                            break;
                        }
                    }
                }
            }

            // --- Step 3: Gemini AI verification/confirmation ---
            if ($useAi && !$matchedCanonical) {
                // Only call Gemini for borderline cases where local matching is uncertain
                // Get candidates with similarity 70-84% (below our threshold)
                $candidates = [];
                foreach ($canonicalStreams as $entry) {
                    similar_text($entry['normalizedName'], $normalizedNew, $percent);
                    if ($percent >= 70 && $percent < 85) {
                        if (StreamDeduplicator::isKnownConflict($entry['stream']->name, $stream->name)) {
                            continue;
                        }
                        if (!StreamDeduplicator::hasConflictingNumbers($entry['normalizedName'], $normalizedNew)) {
                            $candidates[] = $entry['stream']->name;
                        }
                    }
                }

                if (!empty($candidates)) {
                    $aiResult = $this->askGeminiIfDuplicate($stream->name, $candidates);
                    if ($aiResult) {
                        // Find the matched stream
                        foreach ($canonicalStreams as $entry) {
                            if ($entry['stream']->name === $aiResult) {
                                $matchedCanonical = $entry['stream'];
                                $this->line("  <fg=magenta>[AI]</> '{$stream->name}' matched to '{$aiResult}'");
                                break;
                            }
                        }
                    }
                }
            }

            // --- Merge or register as canonical ---
            if ($matchedCanonical) {
                $this->info("[{$checkedCount}] Merging '{$stream->name}' (ID:{$stream->id}) → '{$matchedCanonical->name}' (ID:{$matchedCanonical->id})");

                // Move servers
                foreach ($stream->servers as $server) {
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
                        $server->delete();
                    }
                }

                // Merge categories
                $cats = $stream->categories->pluck('id')->toArray();
                $matchedCanonical->categories()->syncWithoutDetaching($cats);

                // Delete duplicate
                $stream->delete();
                $mergedCount++;
            } else {
                // Register as canonical
                $canonicalStreams[] = [
                    'stream' => $stream,
                    'normalizedName' => $normalizedNew,
                ];
            }
        }

        $this->info("----------------------------------");
        $this->info("Scan complete. Checked: {$checkedCount} | Merged: {$mergedCount} duplicates.");

        // Delete categories that have no streams left
        $emptyCategories = \App\Models\Category::doesntHave('streams')->get();
        $deletedCats = 0;
        foreach ($emptyCategories as $cat) {
            $this->line("Deleting empty category: [{$cat->id}] {$cat->name}");
            $cat->delete();
            $deletedCats++;
        }
        if ($deletedCats > 0) {
            $this->info("Deleted {$deletedCats} empty categories.");
        }

        return 0;
    }

    /**
     * Ask Gemini if the channel name is a duplicate of any candidate.
     * Returns the matching candidate name, or null if no match.
     */
    private function askGeminiIfDuplicate(string $newName, array $candidates): ?string
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        $prompt = "You are a TV channel deduplication expert.\n";
        $prompt .= "Determine if the NEW channel name is the same TV channel as any of the CANDIDATES (different spellings/formats of the same channel).\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- 'Star Sports 1' and 'Star Sports 2' are DIFFERENT channels (different numbers = different channels)\n";
        $prompt .= "- 'Star Sports 1 HD' and 'Star Sports 1' are the SAME channel\n";
        $prompt .= "- 'Sony ESPN' and 'ESPN' might be different, be careful\n\n";
        $prompt .= "NEW: \"{$newName}\"\n";
        $prompt .= "CANDIDATES: " . json_encode($candidates) . "\n\n";
        $prompt .= "Respond in JSON: {\"match\": \"exact candidate name or null\"}";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(6)
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['responseMimeType' => 'application/json']
                ]);

            if ($response->successful()) {
                $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $result = json_decode(trim($text), true);
                    $match = $result['match'] ?? null;
                    if (!empty($match) && $match !== 'null' && in_array($match, $candidates)) {
                        return $match;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fallback
        }

        return null;
    }
}
