<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

    // v1
    Route::prefix('v1')->group(function () {
        // 小程序静默登录
        Route::post('/login-silence', [AuthController::class, 'miniLoginInSilence'])->name('api.v1.mini.mini-login-silence');

        // 小程序登录（未绑定手机号）
        Route::post('/login-bound', [AuthController::class, 'miniLoginOnBound'])->name('api.v1.mini.mini-login-bound');

        // 小程序登录（绑定手机号）
        Route::post('/login', [AuthController::class, 'miniLogin'])->name('api.v1.mini.mini-login');

        // Route::middleware('auth:sanctum')->group(function () {
        //     Route::post('/bind', [AuthController::class, 'bindPhone'])->name('api.v1.mini.bind-phone');
        // });
    });

    Route::prefix('v2')->group(function () {
        // ...
    });
