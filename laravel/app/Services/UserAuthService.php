<?php

namespace App\Services;

use App\Services\Interfaces\WechatServiceInterface;
use App\Models\User;
use App\Models\WechatUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserAuthService
{
    public function __construct(
        private readonly WechatServiceInterface $wechatService,
        private User $userModel,
        private WechatUser $wechatUserModel
    ) {}

    public function handleLogin(string $code) 
    {
        // 获取微信会话
        $session = $this->wechatService->getSession($code);

        // 数据验证
        if (empty($session['open_id']) || empty($session['session_key'])) {
            Log::error('微信会话数据无效', ['session' => $session]);
            throw new \Exception('无效的微信会话数据');
        }

        // 检索微信用户以及他所关联的主表用户
        $wechatUser = $this->wechatUserModel->with('user')
            ->where('openid', $session['open_id'])
            ->first();

        if (!$wechatUser) {
            // 如果不存在，创建新用户和微信用户并关联
            return $this->createUserWithWechatUser($session);
        }

        if (!$wechatUser->user) {
            // 如果存在微信用户但未关联用户，创建用户并关联
            return $this->createUserAndUpdateWechatUser($wechatUser);
        }

        return $wechatUser->user;
    }

    /**
     * 创建新用户和微信用户并关联
     */
    private function createUserWithWechatUser(array $session): User
    {
        try {
            return DB::transaction(function () use ($session) {
                $user = $this->userModel->create(self::buildNewUserAttributes());
                $this->wechatUserModel->create([
                    'user_id' => $user->id,
                    'openid' => $session['open_id'],
                    'session_key' => $session['session_key'],
                    'raw_data' => $session
                ]);
                return $user;
            });
        } catch (\Exception $e) {
            Log::error('创建微信用户和关联用户失败', ['session' => $session, 'error' => $e->getMessage()]);
            throw new \Exception('创建微信用户和关联用户失败', $e->getCode());
        }
    }

    /**
     * 创建新用户并更新关联已经存在的微信用户
     */
    private function createUserAndUpdateWechatUser(WechatUser $wechatUser): User
    {
       try {
            return DB::transction(function () use ($wechatUser) {
                $user = $this->userModel->create(self::buildNewUserAttributes());
                $wechatUser->user_id = $user->id;
                $wechatUser->save();
                return $user;
            });
       } catch (\Exception $e) {
            Log::error('创建用户并更新关联微信用户失败', ['wechatUser' => $wechatUser, 'error' => $e->getMessage()]);
            throw new \Exception('创建用户并更新关联微信用户失败', $e->getCode());
       }
    }

    /**
     * 生成新用户的属性
     */
    private function buildNewUserAttributes() 
    {
        return [
            'name' => self::generateWechatName(),
            'password' => self::generateWechatPassword(),
            'email' => self::generateWechatEmail(),
            'email_verified_at' => now(),
        ];
    }

    private function generateWechatName() 
    {
        return '微信用户'. md5(time());
    }

    private function generateWechatPassword() 
    {
        return Hash::make(md5(time()));
    }

    private function generateWechatEmail() 
    {
        return fake()->unique()->safeEmail();
    }
}