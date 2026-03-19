<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\WixController;
use Illuminate\Support\Facades\Route;

Route::post('/wix_webhook', [WixController::class, 'handleWixWebhooks']);

// Site Notice Banner OAuth
Route::get('/sitenoticebannerau', [WixController::class, 'siteNoticeBannerRedirect']);
Route::get('/sitenoticebannerrd', [WixController::class, 'accessToSiteNoticeBanner']);

// Settings / Config (full widget config)
Route::get('/config', [ConfigController::class, 'show']);
Route::post('/config', [ConfigController::class, 'store']);
Route::put('/config', [ConfigController::class, 'update']);
Route::patch('/config', [ConfigController::class, 'update']);

Route::get('/settings', [ConfigController::class, 'show']);
Route::post('/settings', [ConfigController::class, 'store']);
Route::put('/settings', [ConfigController::class, 'update']);
Route::patch('/settings', [ConfigController::class, 'update']);

// Animation settings (subset)
Route::get('/config/animation', [ConfigController::class, 'showAnimation']);
Route::put('/config/animation', [ConfigController::class, 'updateAnimation']);
Route::patch('/config/animation', [ConfigController::class, 'updateAnimation']);
Route::get('/settings/animation', [ConfigController::class, 'showAnimation']);
Route::put('/settings/animation', [ConfigController::class, 'updateAnimation']);
Route::patch('/settings/animation', [ConfigController::class, 'updateAnimation']);

// Decorations settings (subset)
Route::get('/config/decorations', [ConfigController::class, 'showDecorations']);
Route::put('/config/decorations', [ConfigController::class, 'updateDecorations']);
Route::patch('/config/decorations', [ConfigController::class, 'updateDecorations']);
Route::get('/settings/decorations', [ConfigController::class, 'showDecorations']);
Route::put('/settings/decorations', [ConfigController::class, 'updateDecorations']);
Route::patch('/settings/decorations', [ConfigController::class, 'updateDecorations']);

// Banners config (per-site banner settings)
Route::get('/config/banners', [ConfigController::class, 'showBanners']);
Route::put('/config/banners', [ConfigController::class, 'updateBanners']);
Route::patch('/config/banners', [ConfigController::class, 'updateBanners']);
Route::get('/settings/banners', [ConfigController::class, 'showBanners']);
Route::put('/settings/banners', [ConfigController::class, 'updateBanners']);
Route::patch('/settings/banners', [ConfigController::class, 'updateBanners']);

// Wix instance → site URL (for dashboard iframe preview)
Route::get('/site-url', [WixController::class, 'getSiteUrl']);
