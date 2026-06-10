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
        $this->info("Starting parallel stream link checker...");
        $servers = StreamServer::all();
        $totalChecked = 0;
        $deletedServers = 0;
        
        // Process in batches of 100 links to optimize speed and CPU/Memory usage
        $chunks = $servers->chunk(100);
        
        foreach ($chunks as $chunk) {
            $this->info("Checking batch of " . $chunk->count() . " servers...");
            
            $mh = curl_multi_init();
            $handles = [];
            
            foreach ($chunk as $server) {
                $url = $server->url;
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $handles[$server->id] = [
                        'ch' => null,
                        'server' => $server,
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
                    'server' => $server,
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
                $server = $info['server'];
                $stream = $server->stream;
                $streamName = $stream ? $stream->name : 'Unknown Stream';
                
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
                    $this->error(" -> Dead link found: {$server->url} ({$streamName}). Deleting server...");
                    $server->delete();
                    $deletedServers++;
                } else {
                    $this->info(" -> Server OK: {$streamName}");
                }
                $totalChecked++;
            }
            
            curl_multi_close($mh);
        }
        
        // Delete empty streams
        $deletedStreams = 0;
        $allStreams = Stream::with('servers')->get();
        foreach ($allStreams as $stream) {
            if ($stream->servers->isEmpty()) {
                $this->error("Stream '{$stream->name}' (ID: {$stream->id}) has no working servers left. Deleting stream...");
                $stream->delete();
                $deletedStreams++;
            }
        }

        $this->info("----------------------------------");
        $this->info("Link check complete.");
        $this->info("Checked: {$totalChecked} servers.");
        $this->info("Deleted: {$deletedServers} dead servers.");
        $this->info("Deleted: {$deletedStreams} empty streams.");
        
        return 0;
    }
}
