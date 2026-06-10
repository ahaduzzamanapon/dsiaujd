<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PromoBannerController;

use App\Models\Stream;
use App\Models\PromoBanner;
use App\Models\Category;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $now = Carbon::now();
    
    // Fetch active banners recursively attaching stream details
    $banners = PromoBanner::where('is_active', true)
        ->orderBy('order')
        ->orderBy('id', 'desc')
        ->get();

    foreach ($banners as $banner) {
        $banner->stream1 = $banner->stream1_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream1_id) : null;
        $banner->stream2 = $banner->stream2_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream2_id) : null;
        $banner->stream3 = $banner->stream3_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream3_id) : null;
    }

    // Fetch active and upcoming live events
    $liveEvents = Stream::where('show_in_events', true)
        ->where('is_active', true)
        ->where(function ($query) use ($now) {
            $query->where('is_permanent', true)
                  ->orWhere('expire_time', '>', $now);
        })
        ->with(['servers' => function ($query) {
            $query->orderBy('order');
        }])
        ->orderBy('start_time', 'asc')
        ->get();

    // Fetch categories with their active TV channels
    $categories = Category::with(['streams' => function ($query) use ($now) {
        $query->where('show_in_tv', true)
              ->where('is_active', true)
              ->where(function ($q) use ($now) {
                  $q->where('is_permanent', true)
                    ->orWhere('expire_time', '>', $now);
              })
              ->orderBy('name');
    }])->orderBy('order')->get();

    $settings = \App\Models\AppSetting::first();

    return view('welcome', compact('banners', 'liveEvents', 'categories', 'settings'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Mobile App JSON APIs
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    Route::get('/app-settings', [ApiController::class, 'getSettings']);
    Route::get('/categories', [ApiController::class, 'getCategories']);
    Route::get('/categories/{id}/streams', [ApiController::class, 'getStreamsByCategory']);
    Route::get('/live-events', [ApiController::class, 'getLiveEvents']);
    Route::get('/sports-streams', [ApiController::class, 'getSportsStreams']);
});

/*
|--------------------------------------------------------------------------
| Administrative Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'postLogin'])->name('admin.login.post');
Route::any('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

/*
|--------------------------------------------------------------------------
| Protected Admin Console Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Settings Route
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Promotional Alert Route
    Route::get('/settings/promo-alert', [SettingController::class, 'editPromo'])->name('settings.promo.edit');
    Route::post('/settings/promo-alert', [SettingController::class, 'updatePromo'])->name('settings.promo.update');

    // Promotional Banners CRUD Resource
    Route::resource('promo-banners', PromoBannerController::class);

    // Categories CRUD Resource
    Route::resource('categories', CategoryController::class);

    // Streams CRUD Resource
    Route::post('streams/bulk-delete', [StreamController::class, 'bulkDestroy'])->name('streams.bulk-delete');
    Route::post('streams/merge', [StreamController::class, 'merge'])->name('streams.merge');
    Route::resource('streams', StreamController::class);

    // Sync Console Routes
    Route::get('sync', [DashboardController::class, 'syncConsole'])->name('sync.index');
    Route::post('sync/run', [DashboardController::class, 'runSync'])->name('sync.run');
});
