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

    public function miniLogin(MiniLoginRequest $request)
    {
        // 验证类验证输入
        $validated = $request->validated();
        
        try {
            // 处理微信登录
            $user = $this->userAuthService->handleLogin($validated['code']);
            
            // 生成 token
            $token = $this->userAuthService->generateToken($user);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '微信登录失败',
                'error' => $e->getMessage()
            ], 500);
        }
        
        // 返回 token
        return response()->json([
            'token' => $token
        ]);
    }

    public function bindPhone(Request $request) 
    {
        // 获取认证用户
        $user = $request->user();

        // 验证类验证输入
        $validated = $request->validate([
            'code' => 'required|string'
        ]);

        try {
            // 绑定手机号
            $user = $this->userAuthService->bindPhoneNumber($validated['code'], $user);

            return response()->json([
                'message' => '绑定手机号成功',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '绑定手机号失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
