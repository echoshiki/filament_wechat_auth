<?php

namespace App\Services\Wechat;

use App\Services\Wechat\Support\BaseWechatService;

use EasyWeChat\MiniApp\Application;

class PaymentService extends BaseWechatService
{
    protected $configKey;

    public function __construct(string $configKey)
    {
        $this->configKey = config($configKey);
    }

    public function initApp(): void
    {
        $this->appInstance = new Application($this->config);
    }

}