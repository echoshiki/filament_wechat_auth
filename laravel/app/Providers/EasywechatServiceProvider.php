<?php

namespace App\Services;

use App\Services\Interfaces\WechatServiceInterface;
use App\Services\Wechat\MiniService;
use App\Services\Wechat\PaymentService;
use Illuminate\Support\ServiceProvider;

class EasywechatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 绑定微信小程序服务类
        $this->app->bind(WechatServiceInterface::class, function () {
            return new MiniService(config('wechat.mini'));
        });

        // 当创建 PaymentService 时，自动注入它所需要配置键
        // 原理就是支付类那边不给固定的配置键，而是从这里获取
        // 这样就能更灵活地配置支付的时候用哪个配置
        // $this->app->when(PaymentService::class)
        //     ->needs('$configKey')
        //     ->give("wechat.payment");
    }
}