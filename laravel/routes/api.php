<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

    // v1
    Route::prefix('v1')->group(function () {
        // 小程序登录
        Route::post('/login', [AuthController::class, 'miniLogin'])->name('api.v1.mini.mini-login');

        // 小程序绑定手机号
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/bind', [AuthController::class, 'bindPhone'])->name('api.v1.mini.bind-phone');
        });
    });

    Route::prefix('v2')->group(function () {
        // ...
    });
