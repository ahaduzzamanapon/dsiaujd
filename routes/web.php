<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PromoBannerController;
use App\Http\Controllers\Admin\PendingStreamController;

use App\Models\Stream;
use App\Models\PromoBanner;
use App\Models\Category;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/download_apk', function () {
    return view('download_apk');
})->name('download.apk');

Route::get('/cron/sync-tv-channels', [ApiController::class, 'runTvSyncCron'])->name('cron.sync-tv-channels');
Route::get('/cron/sync-scrapers', [ApiController::class, 'runScrapersCron'])->name('cron.sync-scrapers');
Route::get('/cron/sync-m3u', [ApiController::class, 'runM3uCron'])->name('cron.sync-m3u');


Route::get('/', function () {
    return response()->file(public_path('app/index.html'));
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
    Route::get('/streams', [ApiController::class, 'getAllStreams']);
    Route::get('/live-events', [ApiController::class, 'getLiveEvents']);
    Route::get('/sports-streams', [ApiController::class, 'getSportsStreams']);
    Route::post('/devices/ping', [ApiController::class, 'pingDevice']);
    Route::get('/stream-proxy', [ApiController::class, 'proxyStream'])->name('api.stream-proxy');
    Route::get('/streams/redforce/{stream_id}', [ApiController::class, 'playRedforceStream']);
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
    Route::post('sync/paste', [DashboardController::class, 'bdixPasteSync'])->name('sync.paste');
    Route::get('sync/tasks', [DashboardController::class, 'getSyncTasks'])->name('sync.tasks');
    Route::get('sync/tasks/{id}/log', [DashboardController::class, 'getSyncTaskLog'])->name('sync.log');
    Route::post('sync/clear-history', [DashboardController::class, 'clearSyncHistory'])->name('sync.clear-history');

    // Stream Review Queue Routes
    Route::get('review-queue', [PendingStreamController::class, 'index'])->name('review-queue.index');
    Route::post('review-queue/{id}/approve', [PendingStreamController::class, 'approve'])->name('review-queue.approve');
    Route::post('review-queue/{id}/reject', [PendingStreamController::class, 'reject'])->name('review-queue.reject');
    Route::post('review-queue/reject-all', [PendingStreamController::class, 'rejectAll'])->name('review-queue.reject-all');
    Route::post('review-queue/approve-all', [PendingStreamController::class, 'approveAll'])->name('review-queue.approve-all');
});

/*
|--------------------------------------------------------------------------
| React SPA Catch-All (must be last)
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return response()->file(public_path('app/index.html'));
})->where('any', '^(?!api|admin|cron|download_apk).*$')->name('spa');
