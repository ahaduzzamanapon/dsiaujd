<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@alltv.com'],
            [
                'name' => 'AllTV Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // 2. Create Default App Settings
        AppSetting::updateOrCreate(
            ['id' => 1],
            [
                'app_version' => '1.0.0',
                'is_mandatory_update' => false,
                'update_message' => 'A new update is available with faster streaming servers. Please update your app.',
                'update_url' => 'https://play.google.com/store',
                'welcome_message' => '• Enjoy live events and TV streaming with the latest high-speed servers. Update your app for the best experience. •',
            ]
        );

        // 3. Create Categories
        $categories = [
            ['name' => 'JIOTV+ S3', 'order' => 1],
            ['name' => 'Jio TV+ BD 2', 'order' => 2],
            ['name' => 'FANCODE IND', 'order' => 3],
            ['name' => 'World Country', 'order' => 4],
            ['name' => 'SONY IN', 'order' => 5],
            ['name' => 'Bangla', 'order' => 6],
            ['name' => 'News', 'order' => 7],
            ['name' => 'Kids', 'order' => 8],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['name']] = Category::create($cat);
        }

        // Test stream fallbacks (some public HLS test links)
        $hlsFallbackUrl1 = 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8';
        $hlsFallbackUrl2 = 'https://playertest.longtailvideo.com/adaptive/bipbop/bipbop.m3u8';

        // 4. Seed Streams (Using links from fancode/playlist.json)

        // Stream A: Willow HD (TV + Sports)
        $streamA = Stream::create([
            'name' => 'Willow HD',
            'logo' => 'https://crichd.xyz/assets/images/willow.png',
            'sport_type' => 'cricket',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamA->categories()->attach([$categoryModels['JIOTV+ S3']->id, $categoryModels['SONY IN']->id]);
        StreamServer::create([
            'stream_id' => $streamA->id,
            'name' => 'Willow - Primary Server (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen5.aynaott.com/willowhd/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $streamA->id,
            'name' => 'Willow - Backup Server (HLS)',
            'stream_type' => 'm3u8',
            'url' => 'https://tvsen5.aynaott.com/willowhd/index.m3u8',
            'order' => 2,
        ]);
        StreamServer::create([
            'stream_id' => $streamA->id,
            'name' => 'Willow - Fallback Server 3 (HLS)',
            'stream_type' => 'm3u8',
            'url' => $hlsFallbackUrl1,
            'order' => 3,
        ]);

        // Stream B: PTV Sports (TV + Sports)
        $streamB = Stream::create([
            'name' => 'PTV Sports',
            'logo' => 'https://crichd.xyz/assets/images/ptvsports.png',
            'sport_type' => 'cricket',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamB->categories()->attach([$categoryModels['JIOTV+ S3']->id, $categoryModels['World Country']->id]);
        StreamServer::create([
            'stream_id' => $streamB->id,
            'name' => 'PTV Sports - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen5.aynaott.com/PtvSports/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $streamB->id,
            'name' => 'PTV Sports - Server 2 (HLS)',
            'stream_type' => 'm3u8',
            'url' => 'https://tvsen5.aynaott.com/PtvSports/index.m3u8',
            'order' => 2,
        ]);

        // Stream C: T Sports (TV + Sports)
        $streamC = Stream::create([
            'name' => 'T Sports',
            'logo' => 'https://crichd.xyz/assets/images/tsports.png',
            'sport_type' => 'other',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamC->categories()->attach([$categoryModels['Jio TV+ BD 2']->id, $categoryModels['Bangla']->id]);
        StreamServer::create([
            'stream_id' => $streamC->id,
            'name' => 'T Sports - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen7.aynaott.com/tsports-hd/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $streamC->id,
            'name' => 'T Sports - Server 2 (HLS)',
            'stream_type' => 'm3u8',
            'url' => 'https://tvsen7.aynaott.com/tsports-hd/index.m3u8',
            'order' => 2,
        ]);

        // Stream D: Fox Sports 1 (TV + Sports)
        $streamD = Stream::create([
            'name' => 'Fox Sports 1',
            'logo' => 'https://crichd.xyz/assets/images/foxsports1.png',
            'sport_type' => 'other',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamD->categories()->attach([$categoryModels['World Country']->id]);
        StreamServer::create([
            'stream_id' => $streamD->id,
            'name' => 'Fox Sports 1 - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen7.aynaott.com/foxsports1/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $streamD->id,
            'name' => 'Fox Sports 1 - Server 2 (HLS)',
            'stream_type' => 'm3u8',
            'url' => 'https://tvsen7.aynaott.com/foxsports1/index.m3u8',
            'order' => 2,
        ]);

        // Stream E: Fox Sports 2 (TV + Sports)
        $streamE = Stream::create([
            'name' => 'Fox Sports 2',
            'logo' => 'https://crichd.xyz/assets/images/foxsports2.png',
            'sport_type' => 'other',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamE->categories()->attach([$categoryModels['World Country']->id]);
        StreamServer::create([
            'stream_id' => $streamE->id,
            'name' => 'Fox Sports 2 - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen7.aynaott.com/foxsports2/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $streamE->id,
            'name' => 'Fox Sports 2 - Server 2 (HLS)',
            'stream_type' => 'm3u8',
            'url' => 'https://tvsen7.aynaott.com/foxsports2/index.m3u8',
            'order' => 2,
        ]);

        // Stream F: Fubo TV (TV)
        $streamF = Stream::create([
            'name' => 'Fubo TV',
            'logo' => 'https://crichd.xyz/assets/images/fubo.png',
            'sport_type' => 'other',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => false,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamF->categories()->attach([$categoryModels['World Country']->id]);
        StreamServer::create([
            'stream_id' => $streamF->id,
            'name' => 'Fubo TV - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://live-manifest.production-public.tubi.io/live/d8c035df-1076-4aa6-8628-da2ec80781f9/playlist.m3u8',
            'order' => 1,
        ]);

        // Stream G: Laliga TV (TV + Sports)
        $streamG = Stream::create([
            'name' => 'Laliga TV',
            'logo' => 'https://crichd.xyz/assets/images/laligatv.png',
            'sport_type' => 'football',
            'is_permanent' => true,
            'show_in_tv' => true,
            'show_in_sports' => true,
            'show_in_events' => false,
            'is_active' => true,
        ]);
        $streamG->categories()->attach([$categoryModels['World Country']->id, $categoryModels['SONY IN']->id]);
        StreamServer::create([
            'stream_id' => $streamG->id,
            'name' => 'Laliga TV - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://lchd-314.zenostreams-cdn-001.com/sec/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdHJlYW0iOiJsYWxpZ2EtdHYiLCJleHAiOjE3NzcxMzA0NDF9.E5ctLjX50nGkMsLLbh7wNp2hxqKAgbfkpKWInlyZDjs/laliga-tv.m3u8',
            'order' => 1,
        ]);

        // 5. Seed Live Events (Temporary matches)

        // Event 1: BAN vs AUS (LIVE, start 30 mins ago, expire in 3 hours, shown in Events + Sports)
        $event1 = Stream::create([
            'name' => 'CRICKET || ONE DAY INTERNATIONAL',
            'logo' => 'https://crichd.xyz/assets/images/live.png',
            'sport_type' => 'cricket',
            'team1_name' => 'BAN',
            'team1_logo' => 'https://flagcdn.com/w160/bd.png',
            'team2_name' => 'AUS',
            'team2_logo' => 'https://flagcdn.com/w160/au.png',
            'is_permanent' => false,
            'start_time' => Carbon::now()->subMinutes(30),
            'expire_time' => Carbon::now()->addHours(3),
            'show_in_events' => true,
            'show_in_sports' => true,
            'show_in_tv' => false,
            'is_active' => true,
        ]);
        StreamServer::create([
            'stream_id' => $event1->id,
            'name' => 'T Sports - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen7.aynaott.com/tsports-hd/index.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $event1->id,
            'name' => 'Backup (HLS)',
            'stream_type' => 'm3u8',
            'url' => $hlsFallbackUrl1,
            'order' => 2,
        ]);

        // Event 2: SCO-W vs PAK-W (Upcoming, start in 3 hours, expire in 6 hours, shown in Events)
        $event2 = Stream::create([
            'name' => 'CRICKET || ICC WOMENS T20 WORLD CUP WARM-UP',
            'logo' => 'https://crichd.xyz/assets/images/live.png',
            'sport_type' => 'cricket',
            'team1_name' => 'SCO-W',
            'team1_logo' => 'https://flagcdn.com/w160/gb-sct.png',
            'team2_name' => 'PAK-W',
            'team2_logo' => 'https://flagcdn.com/w160/pk.png',
            'is_permanent' => false,
            'start_time' => Carbon::now()->addHours(3),
            'expire_time' => Carbon::now()->addHours(6),
            'show_in_events' => true,
            'show_in_sports' => false,
            'show_in_tv' => false,
            'is_active' => true,
        ]);
        StreamServer::create([
            'stream_id' => $event2->id,
            'name' => 'PTV Sports - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://tvsen5.aynaott.com/PtvSports/index.m3u8',
            'order' => 1,
        ]);

        // Event 3: Indonesia vs Mozambique (Upcoming, start in 7 hours, expire in 10 hours, shown in Events + Sports)
        $event3 = Stream::create([
            'name' => 'FOOTBALL || INTERNATIONAL FRIENDLY GAMES',
            'logo' => 'https://crichd.xyz/assets/images/live.png',
            'sport_type' => 'football',
            'team1_name' => 'Indonesia',
            'team1_logo' => 'https://flagcdn.com/w160/id.png',
            'team2_name' => 'Mozambique',
            'team2_logo' => 'https://flagcdn.com/w160/mz.png',
            'is_permanent' => false,
            'start_time' => Carbon::now()->addHours(7),
            'expire_time' => Carbon::now()->addHours(10),
            'show_in_events' => true,
            'show_in_sports' => true,
            'show_in_tv' => false,
            'is_active' => true,
        ]);
        StreamServer::create([
            'stream_id' => $event3->id,
            'name' => 'Laliga TV - Server 1 (Iframe)',
            'stream_type' => 'iframe',
            'url' => 'https://hlsplayers.pages.dev/player3?url=https://lchd-314.zenostreams-cdn-001.com/sec/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdHJlYW0iOiJsYWxpZ2EtdHYiLCJleHAiOjE3NzcxMzA0NDF9.E5ctLjX50nGkMsLLbh7wNp2hxqKAgbfkpKWInlyZDjs/laliga-tv.m3u8',
            'order' => 1,
        ]);
        StreamServer::create([
            'stream_id' => $event3->id,
            'name' => 'Fallback (HLS)',
            'stream_type' => 'm3u8',
            'url' => $hlsFallbackUrl2,
            'order' => 2,
        ]);
    }
}
