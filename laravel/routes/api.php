<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Resources\UserResource;

    // v1
    Route::prefix('v1')->group(function () {
        // 小程序静默登录
        Route::post('/login-silence', [AuthController::class, 'miniLoginInSilence'])->name('api.v1.mini.mini-login-silence');
        // 小程序登录（未绑定手机号）
        Route::post('/login-bound', [AuthController::class, 'miniLoginOnBound'])->name('api.v1.mini.mini-login-bound');
        // 小程序登录（绑定手机号）
        Route::post('/login', [AuthController::class, 'miniLogin'])->name('api.v1.mini.mini-login'); 

        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::post('/user', function (Request $request) {
            return new UserResource($request->user());
        })->middleware('auth:sanctum');

    });

    Route::prefix('v2')->group(function () {
        // ...
    });
