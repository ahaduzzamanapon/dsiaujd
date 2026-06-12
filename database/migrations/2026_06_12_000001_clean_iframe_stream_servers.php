<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up hlsplayers.pages.dev iframe URLs to direct m3u8 streams
        $servers = \App\Models\StreamServer::where('url', 'like', '%hlsplayers.pages.dev%')->get();
        foreach ($servers as $server) {
            $parsed = parse_url($server->url);
            parse_str($parsed['query'] ?? '', $query);
            if (isset($query['url']) && filter_var($query['url'], FILTER_VALIDATE_URL)) {
                $server->update([
                    'url' => $query['url'],
                    'stream_type' => 'm3u8'
                ]);
            }
        }

        // 2. Remove all AynaOTT stream servers and empty streams
        \App\Models\StreamServer::where('name', 'AynaOTT')->delete();
        \App\Models\Stream::doesntHave('servers')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback required for data cleanup
    }
};
