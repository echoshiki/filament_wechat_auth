<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\MiniLoginRequest;
use App\Services\UserAuthService;

class AuthController extends Controller
{

    public function __construct(
        private readonly UserAuthService $userAuthService
    ) {}

    /**
     * 静默登录
     * 小程序启动时触发，判断处理创建/关联用户逻辑
     * @param MiniLoginRequest $request
     * @return array 返回加密后的 openid 和是否绑定手机号 isBound
     */
    public function miniLoginInSilence(MiniLoginRequest $request)
    {
        // 验证类验证输入
        $validated = $request->validated();
        
        try {
            // 处理微信登录
            $wechatUser = $this->userAuthService->handleLoginInSilence($validated['code']);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => '微信登录失败',
                'error' => $e->getMessage()
            ], 500);
        }
        
        return response()->json([
            'openid' => encrypt($wechatUser->openid),
            'isBound' => $wechatUser->user->phone !== null ? true : false
        ]);
    }


    /**
     * 小程序登录（未绑定手机号）
     * isBound = false | 登录框微信手机组件按钮触发
     * @param Request $request
     * @return array 返回登录态 token 和用户信息
     */
    public function miniLoginOnBound(Request $request) 
    {
        // 验证类验证输入
        $validated = $request->validate([
            'code' => 'required|string',
            'openid' => 'required|string'
        ]);

        try {
            // 绑定手机号流程
            $user = $this->userAuthService->handleLoginOnBound(
                decrypt($validated['openid']), 
                $validated['code']
            );

            // 生成 token
            $token = $this->userAuthService->generateToken($user, 'mini');

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '登录失败@miniLoginOnBound',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 小程序登录（已绑定过手机号）
     * isBound: true | 登录框普通按钮触发
     * @param Request $request
     * @return array 返回登录态 token 和用户信息
     */
    public function miniLogin(Request $request)
    {
        // 验证类验证输入
        $validated = $request->validate([
            'openid' => 'required|string'
        ]);

        try {
            // 直接查询出用户
            $user = $this->userAuthService->handleLogin(
                decrypt($validated['openid'])
            );

            // 生成 token
            $token = $this->userAuthService->generateToken($user, 'mini');

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '登录失败@miniLogin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
