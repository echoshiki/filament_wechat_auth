<?php

namespace Echoshiki\WechatAuth\Http\Controllers; // 命名空间

use App\Http\Controllers\Controller; // 引入 Laravel 默认 Controller 基类
use Illuminate\Http\Request;
use Echoshiki\WechatAuth\Services\LoginService; // 引入 LoginService

class LoginController extends Controller
{
    protected $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(Request $request)
    {
        $phoneNumber = $request->input('phoneNumber');
        $openId = $request->input('openId');

        $result = $this->loginService->login($phoneNumber, $openId);

        return response()->json($result);
    }

    public function packageInfo()
    {
        return response()->json([
            'name' => 'wechat-auth',
            'version' => '1.0.0',
            'description' => 'Wechat Auth for Laravel',
        ]);
    }
}