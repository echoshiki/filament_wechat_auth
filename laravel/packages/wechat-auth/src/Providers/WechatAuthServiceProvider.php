<?php

namespace Echoshiki\WechatAuth\Providers;  // 命名空间与 composer.json 中 autoload-psr-4 对应
use Illuminate\Support\ServiceProvider;

class WechatAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册服务 (例如单例绑定)
    }

    public function boot(): void
    {
        // 加载迁移文件
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
      	// 加载路由文件
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
      	// 发布配置文件
        $this->publishes([
            __DIR__.'/../../config/wechat-auth.php' => config_path('wechat-auth.php'),
        ], 'wechat-auth-config');
    }
}