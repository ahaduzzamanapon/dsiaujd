<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync M3U Playlists hourly
Schedule::command('m3u:sync https://raw.githubusercontent.com/abusaeeidx/BDxTV/refs/heads/main/roar-zone-playlist.m3u')->hourly();
Schedule::command('m3u:sync https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/CricHD.m3u')->hourly();
Schedule::command('m3u:sync https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/BD.m3u')->hourly();
Schedule::command('m3u:sync https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/main/combined-playlist.m3u')->hourly();
Schedule::command('m3u:sync https://raw.githubusercontent.com/abusaeeidx/Mrgify-BDIX-IPTV/refs/heads/main/playlist.m3u')->hourly();
Schedule::command('fancode:sync')->everyFifteenMinutes();
Schedule::command('streams:check-links')->daily();
