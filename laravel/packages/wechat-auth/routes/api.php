<?php

use Illuminate\Support\Facades\Route;
use Echoshiki\WechatAuth\Http\Controllers\LoginController;

Route::prefix('wechat-auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/package-info', [LoginController::class, 'packageInfo']);
});