<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

use EasyWeChat\MiniApp\Application;

    // v1
    Route::prefix('v1')->group(function () {
        // 小程序静默登录
        Route::post('/login-silence', [AuthController::class, 'miniLoginInSilence'])->name('api.v1.mini.mini-login-silence');

        // 小程序登录（未绑定手机号）
        Route::post('/login-bound', [AuthController::class, 'miniLoginOnBound'])->name('api.v1.mini.mini-login-bound');

        // 小程序登录（绑定手机号）
        Route::post('/login', [AuthController::class, 'miniLogin'])->name('api.v1.mini.mini-login');

        Route::post('/test', function() {
            $app = new Application([
                'app_id' => "wxaf540566d50d27c7",
                'secret' => "1e841f0227094470611ae59ad1ce1e99"
            ]);
            $code = (string) request()->get('code');

            // 可以获取到 openid 和 session_key
            // return $app->getUtils()->codeToSession($code);

            // 写全接口
            return $app->getClient()->get('sns/jscode2session', [
                'appid' => "wxaf540566d50d27c7",
                'secret' => "1e841f0227094470611ae59ad1ce1e99",
                'js_code' => $code,
                'grant_type' => 'authorization_code'
            ]);

            // return $app->getClient()->postJson('wxa/business/getuserphonenumber', [
            //     'code' => $code,
            // ]);
        });
        
    });

    Route::prefix('v2')->group(function () {
        // ...
    });
