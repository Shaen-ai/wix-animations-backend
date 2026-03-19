<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'ok']));

Route::get('/config', [ConfigController::class, 'show']);
Route::post('/config', [ConfigController::class, 'store']);
Route::put('/config', [ConfigController::class, 'update']);
Route::patch('/config', [ConfigController::class, 'update']);

Route::get('/banners', [BannerController::class, 'index']);
