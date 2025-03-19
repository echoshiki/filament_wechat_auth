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

    /**
     * 处理微信静默登录
     * 会话 session 信息，不涉及手机号
     * @param string $code
     * @return User
     */
    public function handleLoginInSilence(string $code) 
    {
        // 获取微信会话
        $session = $this->wechatService->getSession($code);

        // 数据验证
        if (empty($session['openid']) || empty($session['session_key'])) {
            Log::error('微信会话数据无效', ['session' => $session]);
            throw new \Exception('无效的微信会话数据');
        }

        // 检索微信用户以及他所关联的主表用户
        $wechatUser = $this->wechatUserModel->with('user')
            ->where('openid', $session['openid'])
            ->first();

        if (!$wechatUser) {
            // 如果不存在，创建新用户和微信用户并关联
            return $this->createUserWithWechatUser($session);
        }

        if (!$wechatUser->user) {
            // 如果存在微信用户但未关联用户，创建用户并关联
            return $this->createUserAndUpdateWechatUser($wechatUser);
        }

        return $wechatUser;
    }

    /**
     * 创建新用户和微信用户并关联
     * @param array $session
     * @return WechatUser
     */
    private function createUserWithWechatUser(array $session): WechatUser
    {
        try {
            return DB::transaction(function () use ($session) {
                $user = $this->userModel->create(self::buildNewUserAttributes());
                $wechatUser = $this->wechatUserModel->create([
                    'user_id' => $user->id,
                    'openid' => $session['openid'],
                    'session_key' => $session['session_key']
                ]);
                $wechatUser->load('user');
                return $wechatUser;
            });
        } catch (\Exception $e) {
            Log::error('创建微信用户和关联用户失败', ['session' => $session, 'error' => $e->getMessage()]);
            throw new \Exception('创建微信用户和关联用户失败', $e->getCode());
        }
    }

    /**
     * 创建新用户并更新关联已经存在的微信用户
     * @param WechatUser $wechatUser
     * @return WechatUser
     */
    private function createUserAndUpdateWechatUser(WechatUser $wechatUser): WechatUser
    {
       try {
            return DB::transaction(function () use ($wechatUser) {
                $user = $this->userModel->create(self::buildNewUserAttributes());
                $wechatUser->user_id = $user->id;
                $wechatUser->save();
                $wechatUser->load('user');
                return $wechatUser;
            });
       } catch (\Exception $e) {
            Log::error('创建用户并更新关联微信用户失败', ['wechatUser' => $wechatUser, 'error' => $e->getMessage()]);
            throw new \Exception('创建用户并更新关联微信用户失败', $e->getCode());
       }
    }

    /**
     * 生成新用户的属性
     * @return array
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

    /**
     * 生成 api token
     * @param User $user
     * @return string
     */
    public function generateToken(User $user, string $deviceName = "mini") 
    {
        return $user->createToken($deviceName)->plainTextToken;
    }


    /**
     * 登录：通过 openid 绑定手机号码
     * @param string $openid
     * @return User
     */
    public function handleLoginOnBound(string $openid, string $code)
    {
        // 数据验证
        if (empty($openid)) {
            throw new \Exception('丢失重要参数 openid');
        }
        $user = $this->userModel->withWechatUserOpenid($openid)->first();
        if (!$user) {
            throw new \Exception('微信用户的关联用户不存在');
        }
        $this->bindPhoneNumber($code, $user);

        return $user;
    }

    /**
     * 绑定手机号
     * 登录后绑定手机号
     * @param string $code
     * @return User
     */
    public function bindPhoneNumber(string $code, User $user) 
    {
        // 获取手机号
        $phoneInfo = $this->wechatService->getPhoneNumber($code);

        // 数据验证
        if (empty($phoneInfo['phone_info'])) {
            Log::error('获取手机号失败', ['code' => $code, 'phoneInfo' => $phoneInfo]);
            throw new \Exception('获取手机号失败');
        }

        // 更新手机号
        $user->phone = $phoneInfo['phone_info']['phoneNumber'];
        $user->phone_verified_at = now();
        $user->save();

        return $user;
    }

    public function handleLogin(string $openid)
    {
        // 数据验证
        if (empty($openid)) {
            throw new \Exception('丢失重要参数 openid');
        }

        $user = $this->userModel->withWechatUserOpenid($openid)->first();

        if (!$user) {
            throw new \Exception('微信用户的关联用户不存在');
        }

        if ($user->phone === null) {
            throw new \Exception('未绑定手机号');
        }

        return $user;
    }

}