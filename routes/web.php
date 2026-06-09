<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
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
