<?php

namespace Echoshiki\WechatAuth\Services;

class LoginService
{
    public function login(string $phoneNumber, string $openId): array
    {
        // 简单的占位符实现，后续完善登录逻辑
        return [
            'status' => 'success',
            'message' => 'Login successful (placeholder)',
            'user' => [
                'phoneNumber' => $phoneNumber,
                'openId' => $openId,
            ],
            // Token 将在后续 Sanctum 集成步骤中添加
        ];
    }
}