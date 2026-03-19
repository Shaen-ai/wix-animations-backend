<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\WixController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'ok']));

// Site Notice Banner OAuth
Route::get('/sitenoticebannerau', [WixController::class, 'siteNoticeBannerRedirect']);
Route::get('/sitenoticebannerrd', [WixController::class, 'accessToSiteNoticeBanner']);

Route::get('/config', [ConfigController::class, 'show']);
Route::post('/config', [ConfigController::class, 'store']);
Route::put('/config', [ConfigController::class, 'update']);
Route::patch('/config', [ConfigController::class, 'update']);

Route::get('/banners', [BannerController::class, 'index']);
