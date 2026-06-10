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
        $this->info("Starting stream link checker...");
        $servers = StreamServer::all();
        $totalChecked = 0;
        $deletedServers = 0;
        
        $streamIdsToCheck = [];

        foreach ($servers as $server) {
            $stream = $server->stream;
            $streamName = $stream ? $stream->name : 'Unknown Stream';
            $this->info("Checking: {$streamName} -> {$server->name}");
            
            $isActive = $this->checkLink(
                $server->url,
                $server->http_referer,
                $server->http_origin
            );

            if (!$isActive) {
                $this->error(" -> Dead link found: {$server->url}. Deleting server...");
                if ($stream) {
                    $streamIdsToCheck[] = $stream->id;
                }
                $server->delete();
                $deletedServers++;
            } else {
                $this->info(" -> Server OK");
            }
            $totalChecked++;
        }

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

    /**
     * Check if a stream URL is active and responding.
     */
    private function checkLink(string $url, ?string $referer = null, ?string $origin = null): bool
    {
        // If it is not a valid URL structure, mark as dead immediately
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 seconds connection timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request to save time and bandwidth
            
            $headers = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ];

            if ($referer) {
                curl_setopt($ch, CURLOPT_REFERER, $referer);
            }
            if ($origin) {
                $headers[] = "Origin: {$origin}";
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // 200 OK, 206 Partial Content, 302 Found/Redirect, or 403 Forbidden (some streams return 403 on direct checks but work with players)
            return $statusCode >= 200 && $statusCode < 400 || $statusCode === 403;
        } catch (\Exception $e) {
            return false;
        }
    }
}
