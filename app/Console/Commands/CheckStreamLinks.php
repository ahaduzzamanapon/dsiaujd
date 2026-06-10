<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stream;
use App\Models\StreamServer;

class CheckStreamLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:check-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate all stream server links and delete dead ones. Streams with no working servers are also deleted.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->info("Starting parallel stream link checker...");
        
        // Fetch only needed columns to keep memory minimal
        $servers = StreamServer::select('id', 'url', 'http_referer', 'http_origin', 'stream_id')->get();
        $totalChecked = 0;
        $deletedServersCount = 0;
        
        $deadServerIds = [];
        
        // Process in batches of 20 links to keep memory and socket footprint very low
        $chunks = $servers->chunk(20);
        
        foreach ($chunks as $chunk) {
            $this->info("Checking batch of " . $chunk->count() . " servers...");
            
            $mh = curl_multi_init();
            $handles = [];
            
            foreach ($chunk as $server) {
                $url = $server->url;
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $handles[$server->id] = [
                        'ch' => null,
                        'url' => $url
                    ];
                    continue;
                }
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 4); // 4 seconds total execution timeout
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 2 seconds connect timeout
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
                
                $headers = [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ];

                if ($server->http_referer) {
                    curl_setopt($ch, CURLOPT_REFERER, $server->http_referer);
                }
                if ($server->http_origin) {
                    $headers[] = "Origin: {$server->http_origin}";
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                curl_multi_add_handle($mh, $ch);
                $handles[$server->id] = [
                    'ch' => $ch,
                    'url' => $url
                ];
            }
            
            // Execute parallel requests
            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);
            
            // Evaluate results
            foreach ($handles as $serverId => $info) {
                $ch = $info['ch'];
                $url = $info['url'];
                
                $isActive = false;
                if ($ch === null) {
                    $isActive = false;
                } else {
                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);
                    
                    // 200 OK, 206 Partial Content, 302 Found/Redirect, or 403 Forbidden
                    $isActive = ($statusCode >= 200 && $statusCode < 400) || $statusCode === 403;
                }
                
                if (!$isActive) {
                    $this->error(" -> Dead link found: {$url}. Queueing for deletion...");
                    $deadServerIds[] = $serverId;
                    $deletedServersCount++;
                } else {
                    $this->info(" -> Server OK: {$url}");
                }
                $totalChecked++;
            }
            
            curl_multi_close($mh);
            
            // Unset variables to free memory immediately
            unset($handles);
            unset($mh);
            gc_collect_cycles();
        }
        
        // 1. Delete dead servers in batch
        if (!empty($deadServerIds)) {
            $this->info("Deleting " . count($deadServerIds) . " dead servers from the database...");
            foreach (array_chunk($deadServerIds, 250) as $subDeadIds) {
                StreamServer::whereIn('id', $subDeadIds)->delete();
            }
        }
        
        // 2. Delete empty streams (channels with no servers left) in batch
        $this->info("Checking for channels with no active stream links...");
        $emptyStreamIds = Stream::doesntHave('servers')->pluck('id')->toArray();
        $deletedStreamsCount = count($emptyStreamIds);
        
        if ($deletedStreamsCount > 0) {
            $this->info("Deleting {$deletedStreamsCount} empty channels from the database...");
            foreach (array_chunk($emptyStreamIds, 250) as $subEmptyIds) {
                Stream::destroy($subEmptyIds);
            }
        }

        $this->info("----------------------------------");
        $this->info("Link check complete.");
        $this->info("Checked: {$totalChecked} servers.");
        $this->info("Deleted: {$deletedServersCount} dead servers.");
        $this->info("Deleted: {$deletedStreamsCount} empty channels.");
        
        return 0;
    }
}
