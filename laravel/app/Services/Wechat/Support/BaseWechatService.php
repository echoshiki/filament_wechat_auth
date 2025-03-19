<?php

namespace App\Services\Wechat\Support;

use Illuminate\Support\Facades\Log;

// 提供基础功能抽象类
// 抽象类：标准指定/子类必须自己实现它所规定的功能/不能实例化
abstract class BaseWechatService
{
    // 声明一个受保护的属性，存储微信配置
    protected array $config;

    // 声明一个受保护的属性，存储微信应用实例
    // 在子类里面被自动初始化
    protected mixed $appInstance;

    public function __construct(string $configKey = 'mini')
    {
        // 获取配置，默认为空数组
        $this->config = config("wechat.{$configKey}", []);
        $this->validateConfig();
        $this->initApp();
    }

    // 初始化微信应用实例，由子类实现
    abstract protected function initApp(): void;

    protected function validateConfig(): void
    {    
        if (empty($this->config['app_id']) || empty($this->config['secret'])) {
            throw new \InvalidArgumentException('微信配置不完整，缺少必要参数.');
        }
    }

    // 统一调用 API，并处理异常
    protected function callApi(callable $callback, string $apiName = '')
    {
        try {
            // 调用回调函数
            return $callback($this->appInstance);
        } catch (\Throwable $e) {
            // 记录错误日志
            Log::error("调用微信接口失败：{$apiName}", [
                'error' => $e->getMessage(),
                'config' => $this->config,
            ]);
            // 抛出运行时异常
            throw new \RuntimeException("调用微信 API {$apiName} 失败：{$e->getMessage()}");
        }
    }
}