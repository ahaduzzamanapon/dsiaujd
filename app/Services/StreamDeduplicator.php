<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;

class StreamDeduplicator
{
    /**
     * Normalize channel name by removing spaces, non-alphanumeric chars, and noise words.
     */
    public static function normalizeName(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/\([^)]+\)/', '', $name); // remove (..)
        $name = preg_replace('/\[[^\]]+\]/', '', $name); // remove [..]
        
        // Split name into words
        $words = preg_split('/[^a-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        
        $noise = ['hd', 'sd', 'fhd', 'uhd', '4k', 'live', 'tv', 'channel', 'bengali', 'bangla', 'english', 'hindi', 'nepal', 'pakistan', 'india', 'asia', 'online', 'scr', 'stream', 'iptv'];
        
        $filteredWords = [];
        foreach ($words as $word) {
            if (!in_array($word, $noise) || (count($words) === 1)) {
                $filteredWords[] = $word;
            }
        }
        
        if (empty($filteredWords)) {
            $filteredWords = $words;
        }
        
        return implode('', $filteredWords);
    }

    /**
     * Check if two normalized names contain different numbers.
     */
    public static function hasConflictingNumbers(string $str1, string $str2): bool
    {
        preg_match_all('/\d+/', $str1, $num1);
        preg_match_all('/\d+/', $str2, $num2);
        
        $n1 = implode('', $num1[0]);
        $n2 = implode('', $num2[0]);
        
        return $n1 !== $n2;
    }

    /**
     * Match keyword rules to map to standard category tabs.
     */
    public static function resolveCanonicalCategory(string $name, string $suggestedCategory = ''): string
    {
        $nameLower = strtolower($name);
        $catLower = strtolower($suggestedCategory);

        $mappings = [
            'Sports' => ['sport', 'cricket', 'football', 'willow', 'espn', 'fifa', 'tsports', 'premier', 'cup', 'league'],
            'Kids' => ['kids', 'cartoon', 'nick', 'pogo', 'hungama', 'disney', 'babytv', 'sonic', 'super hungama'],
            'Islamic' => ['islam', 'peace tv', 'madani', 'makkah', 'madina', 'iqra'],
            'Music' => ['music', 'mastiii', '9xm', '9x', 'sangeet', 'dhoom'],
            'News' => ['news', 'bbc news', 'cnn', 'aljazeera', 'dbx', 'times', 'wion'],
            'Bangla' => ['bangla', 'desh', 'independent', 'somoy', 'atn', 'mohona', 'bijoy', 'ekattor', 'gtv', 'gazi', 'rtv', 'ntv', 'channel 24', 'channel i', 'channel 9', 'sa tv', 'deepto', 'jamuna', 'dbc', 'news 24', 'nexus', 'ananda', 'global', 'duronto', 'ekhon'],
            'Hindi' => ['star plus', 'colors', 'zee tv', 'sony', 'sab', 'star gold', 'colors cineplex', 'zee cinema', 'zoom', 'zing', 'star bharat', 'shemaroo'],
            'English' => ['hbo', 'star movies', 'axn', 'bbc', 'cnn', 'aljazeera', 'dw', 'nhk', 'trt', 'sky news', 'bloomberg', 'discovery', 'history', 'tlc', 'travel xp', 'fashion', 'love nature']
        ];

        // 1. Check channel name keywords first
        foreach ($mappings as $canonical => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($nameLower, $keyword)) {
                    return $canonical;
                }
            }
        }

        // 2. Check suggested category next
        foreach ($mappings as $canonical => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($catLower, $keyword)) {
                    return $canonical;
                }
            }
        }

        // 3. Fallback
        if (!empty($suggestedCategory)) {
            $capitalized = ucfirst(trim($suggestedCategory));
            $standard = ['Sports', 'Bangla', 'Hindi', 'English', 'Kids', 'Islamic', 'News', 'Music', 'Entertainment', 'Live Channel', 'Fresh'];
            if (in_array($capitalized, $standard)) {
                return $capitalized;
            }
        }

        return 'Live Channel';
    }

    /**
     * Query Gemini AI to matching channel name and category.
     */
    public static function resolveUsingGemini(string $name, string $suggestedCategory, array $existingNames): ?array
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        $prompt = "You are a TV Channel Deduplication & Classification AI. Your job is to match a new channel to existing database names or determine if it is new, and classify it.\n\n";
        $prompt .= "Standard Categories: Sports, Bangla, Hindi, English, Kids, Islamic, News, Music, Entertainment.\n\n";
        $prompt .= "Input:\n";
        $prompt .= "New Channel Name: \"" . $name . "\"\n";
        $prompt .= "Suggested Category: \"" . $suggestedCategory . "\"\n";
        $prompt .= "Existing Channels in Database: " . json_encode($existingNames) . "\n\n";
        $prompt .= "Response rules:\n";
        $prompt .= "1. If the New Channel Name is a duplicate of one of the existing channels in the list (even with different spellings, punctuation, or spaces like 'Star Sports 1 HD' matching 'Star Sports 1'), return that exact matching name in 'matched_name'. Otherwise return null.\n";
        $prompt .= "2. Be very careful: numbered channels like 'Star Sports 1' and 'Star Sports 2' are NOT duplicates. They must not match.\n";
        $prompt .= "3. Return the resolved category in 'category'. It MUST be one of the standard categories listed above.\n\n";
        $prompt .= "Respond ONLY in this JSON format:\n";
        $prompt .= "{\n  \"matched_name\": \"Name from list or null\",\n  \"category\": \"Standard Category\"\n}";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->timeout(8)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $result = json_decode(trim($text), true);
                    if (isset($result['category'])) {
                        return [
                            'matched_name' => (!empty($result['matched_name']) && $result['matched_name'] !== 'null') ? $result['matched_name'] : null,
                            'category' => $result['category']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions, fallback will handle it
        }

        return null;
    }

    /**
     * Syncs a channel by deduplicating name, updating category, and appending the server.
     */
    public static function syncChannelWithDeduplication(
        string $name, 
        ?string $logo, 
        string $srvName, 
        string $srvUrl, 
        ?string $httpReferer = null, 
        ?string $httpOrigin = null, 
        string $suggestedCategory = ''
    ): Stream {
        // 1. Try resolving using Gemini AI if key is present
        $aiResult = null;
        $apiKey = env('GEMINI_API_KEY');
        if (!empty($apiKey)) {
            $existingNames = Stream::pluck('name')->toArray();
            $aiResult = self::resolveUsingGemini($name, $suggestedCategory, $existingNames);
        }

        $matchedStream = null;
        $canonicalCategoryName = null;

        if ($aiResult) {
            $canonicalCategoryName = $aiResult['category'];
            if (!empty($aiResult['matched_name'])) {
                $matchedStream = Stream::where('name', $aiResult['matched_name'])->first();
            }
        }

        // 2. Fallback to local matching if AI did not resolve
        if (!$canonicalCategoryName) {
            $canonicalCategoryName = self::resolveCanonicalCategory($name, $suggestedCategory);
        }

        if (!$matchedStream) {
            $normalizedNew = self::normalizeName($name);
            
            // Check for exact name match
            $exactStream = Stream::where('name', $name)->first();
            if ($exactStream) {
                $matchedStream = $exactStream;
            } else {
                $streams = Stream::all();
                foreach ($streams as $dbStream) {
                    $normalizedDb = self::normalizeName($dbStream->name);
                    
                    if ($normalizedDb === $normalizedNew) {
                        $matchedStream = $dbStream;
                        break;
                    }
                    
                    similar_text($normalizedDb, $normalizedNew, $percent);
                    if ($percent >= 85) {
                        if (!self::hasConflictingNumbers($normalizedDb, $normalizedNew)) {
                            $matchedStream = $dbStream;
                            break;
                        }
                    }
                }
            }
        }

        // 3. Create or update Stream
        $category = Category::firstOrCreate(['name' => $canonicalCategoryName]);

        if ($matchedStream) {
            if (empty($matchedStream->logo) && !empty($logo)) {
                $matchedStream->update(['logo' => $logo]);
            }
            $stream = $matchedStream;
        } else {
            $isSports = ($canonicalCategoryName === 'Sports');
            $stream = Stream::create([
                'name' => $name,
                'logo' => $logo ?: null,
                'sport_type' => 'other',
                'is_permanent' => true,
                'show_in_events' => false,
                'show_in_sports' => $isSports,
                'show_in_tv' => true,
                'is_active' => true,
            ]);
        }

        $stream->categories()->syncWithoutDetaching([$category->id]);

        // 4. Attach StreamServer
        $server = StreamServer::where('stream_id', $stream->id)
            ->where('url', $srvUrl)
            ->first();

        if (!$server) {
            $maxOrder = StreamServer::where('stream_id', $stream->id)->max('order');
            
            // Auto name generic "Server"
            if ($srvName === 'Server' || $srvName === 'Server 1') {
                $existingCount = StreamServer::where('stream_id', $stream->id)->count();
                $srvName = 'Server ' . ($existingCount + 1);
            }

            StreamServer::create([
                'stream_id' => $stream->id,
                'name' => $srvName,
                'stream_type' => 'm3u8',
                'url' => $srvUrl,
                'http_referer' => $httpReferer,
                'http_origin' => $httpOrigin,
                'order' => is_null($maxOrder) ? 0 : ($maxOrder + 1),
            ]);
        } else {
            $server->update([
                'http_referer' => $httpReferer,
                'http_origin' => $httpOrigin,
            ]);
        }

        return $stream;
    }
}
