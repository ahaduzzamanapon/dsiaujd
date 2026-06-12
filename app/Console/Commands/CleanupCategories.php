<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Stream;

class CleanupCategories extends Command
{
    protected $signature = 'categories:cleanup';
    protected $description = 'Use Gemini AI to reclassify streams in non-standard categories, then delete the orphan categories';

    // The only valid/standard categories we want to keep
    const STANDARD = [
        'Sports', 'Bangla', 'Hindi', 'English', 'Kids',
        'Islamic', 'News', 'Music', 'Entertainment', 'Live Channel'
    ];

    public function handle()
    {
        $apiKey = env('GEMINI_API_KEY');

        // 1. Find all non-standard categories (case-insensitive match)
        $allCategories = Category::with('streams')->get();
        $standardLower = array_map('strtolower', self::STANDARD);

        $nonStandard = $allCategories->filter(function ($cat) use ($standardLower) {
            return !in_array(strtolower(trim($cat->name)), $standardLower);
        });

        if ($nonStandard->isEmpty()) {
            $this->info('No non-standard categories found. Everything is clean!');
            return 0;
        }

        $this->info("Found {$nonStandard->count()} non-standard categories:");
        foreach ($nonStandard as $cat) {
            $this->line("  [{$cat->id}] {$cat->name} ({$cat->streams->count()} streams)");
        }
        $this->line('');

        // 2. Ensure all standard categories exist in DB
        $standardMap = []; // 'Sports' => Category model
        foreach (self::STANDARD as $stdName) {
            $standardMap[$stdName] = Category::firstOrCreate(['name' => $stdName]);
        }

        $reclassified = 0;
        $deleted = 0;

        // 3. For each non-standard category, reclassify its streams
        foreach ($nonStandard as $cat) {
            $streams = $cat->streams;

            if ($streams->isEmpty()) {
                // Empty already — just delete
                $this->line("  Deleting empty category: [{$cat->id}] {$cat->name}");
                $cat->delete();
                $deleted++;
                continue;
            }

            $this->info("Processing category: [{$cat->id}] {$cat->name} ({$streams->count()} streams)...");

            foreach ($streams as $stream) {
                // Ask Gemini which standard category this stream belongs to
                $assigned = $this->askGeminiCategory($stream->name, $cat->name, $apiKey);

                if (!$assigned) {
                    // Fallback: keep in "Live Channel"
                    $assigned = 'Live Channel';
                }

                $targetCat = $standardMap[$assigned];
                $this->line("  ✓ '{$stream->name}' → {$assigned}");

                // Attach to correct category (without detaching others)
                $stream->categories()->syncWithoutDetaching([$targetCat->id]);

                // Detach from the non-standard category
                $stream->categories()->detach($cat->id);

                $reclassified++;
            }

            // 4. Now the category should be empty — delete it
            $cat->refresh();
            if ($cat->streams()->count() === 0) {
                $this->line("  Deleting now-empty category: [{$cat->id}] {$cat->name}");
                $cat->delete();
                $deleted++;
            }
        }

        $this->line('');
        $this->info("----------------------------------");
        $this->info("Done! Reclassified: {$reclassified} streams | Deleted: {$deleted} categories.");
        return 0;
    }

    private function askGeminiCategory(string $channelName, string $currentCategory, ?string $apiKey): ?string
    {
        if (empty($apiKey)) {
            // Fallback: use keyword logic
            return \App\Services\StreamDeduplicator::resolveCanonicalCategory($channelName, $currentCategory);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $standardList = implode(', ', self::STANDARD);
        $prompt = "You are a TV channel classification expert.\n";
        $prompt .= "Given a TV channel name, assign it to exactly ONE of these standard categories:\n";
        $prompt .= "{$standardList}\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- 'Star Sports', 'ESPN', 'TSports', 'Willow' → Sports\n";
        $prompt .= "- Bangladeshi channels (RTV, NTV, ATN, GTV, Somoy, Jamuna, Ekattor) → Bangla\n";
        $prompt .= "- Zee TV, Star Plus, Colors, Sony → Hindi\n";
        $prompt .= "- BBC, CNN, Al Jazeera, Discovery, HBO → English\n";
        $prompt .= "- Nick, Disney, Cartoon Network, Pogo → Kids\n";
        $prompt .= "- Peace TV, Iqra, Madani → Islamic\n";
        $prompt .= "- Any news channel → News\n";
        $prompt .= "- Music channels → Music\n";
        $prompt .= "- If unsure → Live Channel\n\n";
        $prompt .= "Channel Name: \"{$channelName}\"\n";
        $prompt .= "Current Category (may be wrong): \"{$currentCategory}\"\n\n";
        $prompt .= "Respond in JSON only: {\"category\": \"one of the standard categories\"}";

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
                    $cat = $result['category'] ?? null;
                    if ($cat && in_array($cat, self::STANDARD)) {
                        return $cat;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fallback
        }

        // Fallback to keyword-based
        return \App\Services\StreamDeduplicator::resolveCanonicalCategory($channelName, $currentCategory);
    }
}
