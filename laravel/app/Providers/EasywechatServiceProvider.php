<?php

namespace App\Providers;

use App\Services\Interfaces\WechatServiceInterface;
use App\Services\Wechat\MiniService;
use Illuminate\Support\ServiceProvider;

class EasywechatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 绑定微信小程序服务类
        $this->app->bind(WechatServiceInterface::class, function () {
            return new MiniService('mini');
        });
    }
}