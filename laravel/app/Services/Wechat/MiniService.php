<?php

namespace App\Services\Wechat;

use App\Services\Wechat\Support\BaseWechatService;
// 统一函数名规范的接口
use App\Services\Interfaces\WechatServiceInterface;

use EasyWeChat\MiniApp\Application;
use Illuminate\Support\Facades\Log;

class MiniService extends BaseWechatService implements WechatServiceInterface
{
    // 实现基础类规定的接口
    // 继承基础类配置，使用 easywechat 初始化小程序应用
    public function initApp(): void
    {
        // 读取到了配置
        $this->appInstance = new Application($this->config);
    }

    /**
     * 获取会话信息
     * 返回值示例
     * {
     *    "openid":"xxxxxx",
     *    "session_key":"xxxxx",
     *    "unionid":"xxxxx",
     *    "errcode":0,
     *    "errmsg":"xxxxx"
     * }
     */
    public function getSession(string $code): array
    {
        // $this->appInstance 由基础类封装在 callApi() 中
        // 直接调用 callApi 即可
        return $this->callApi(function (Application $app) use ($code) {

            // Log::info('请求 getSession 方法', ['code' => $code, 'config' => $this->config, 'app'=> $app]);

            $response = $app->getUtils()->codeToSession($code);
            return $response;

        }, 'jscode2session');
    }

    /**
     * 获取手机号码
     * 返回值示例
     * {
     *     "errcode":0,
     *     "errmsg":"ok",
     *     "phone_info": {
     *         "phoneNumber":"xxxxxx",
     *         "purePhoneNumber": "xxxxxx",
     *         "countryCode": 86,
     *         "watermark": {
     *             "timestamp": 1637744274,
     *             "appid": "xxxx"
     *         }
     *     }
     * }
     */
    public function getPhoneNumber(string $code): array
    {
        return $this->callApi(function (Application $app) use ($code) {
            $response = $app->getClient()->postJson('wxa/business/getuserphonenumber', [
                'code' => $code,
            ]);
            Log::info('getPhoneNumber 返回值', ['response' => $response]);

            return $response->toArray();

        }, 'getuserphonenumber');
    }
}