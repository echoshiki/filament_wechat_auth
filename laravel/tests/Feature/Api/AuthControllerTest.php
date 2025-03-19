<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Services\UserAuthService;
use App\Models\User;
use App\Models\WechatUser;
use App\Services\Interfaces\WechatServiceInterface;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private $wechatServiceMock;

    private $code;
    private $sessionData;
    private $phoneData;

    private $user;
    private $wechatUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 测试数据
        $this->code = 'test_code';
        $this->sessionData = [
            "errcode" => 0,
            "errmsg" => "ok",
            'open_id' => 'test_open_id',
            'session_key' => 'test_session_key'  
        ];
        $this->phoneData = [
            "errcode" => 0,
            "errmsg" => "ok",
            "phone_info" => [
                "phoneNumber" => "13800138000",
                "purePhoneNumber" => "13800138000",
                "countryCode" => 86,
                "watermark" => [
                    "timestamp" => 1637744274,
                    "appid" => "xxxx"
                ]
            ]
        ];

        // 实例化一些模型方便使用
        $this->user = new User();
        $this->wechatUser = new WechatUser();

        // 模拟微信服务接口
        $this->wechatServiceMock = Mockery::mock(WechatServiceInterface::class);

        // 将模拟微信服务接口绑定到容器
        $this->app->instance(WechatServiceInterface::class, $this->wechatServiceMock);

        // 模拟微信登录返回数据
        $this->wechatServiceMock->shouldReceive('getSession')
            ->with($this->code)
            ->andReturn($this->sessionData);

        // 模拟微信手机号返回数据
        $this->wechatServiceMock->shouldReceive('getPhoneNumber')
            ->with($this->code)
            ->andReturn($this->phoneData);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    // 测试静默登录控制器（新用户）
    // 预期返回加密后的加密后的 openid & isBound
    public function test_mini_login_silence_new_user()
    {
        $response = $this->postJson('api/v1/login-silence', [
            'code' => $this->code
        ]);

        // 断言是否返回了符合预期的数据键
        $response->assertStatus(200)->assertJsonStructure(['openid', 'isBound']);

        // 断言数据库中存在对应的微信用户记录
        $wechatUser = $this->wechatUser->where('openid', $this->sessionData['openid'])->first();
        $this->assertDatabaseHas('wechat_users', ['openid' => $this->sessionData['openid']]);

        // 是否存在关联用户
        $this->assertDatabaseHas('users', ['id' => $wechatUser->user_id]);

        // 断言 isBound 是否正确
        $user = $this->user->where('id', $wechatUser->user_id)->first();
        $this->assertEquals($user->phone, $response->json('isBound') ? $user->phone : null);
    }

    // 测试静默登录控制器（用户存在）
    // 预期返回加密后的加密后的 openid & isBound
    public function test_mini_login_silence_exist_user()
    {
        // 创建测试用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '123123',
            'phone' => '13218900000'
        ]);

        $wechatUser = $this->wechatUser->create([
            'user_id' => $user->id,
            'openid' => 'test_open_id',
            'session_key' => 'test_session_key'
        ]);

        $response = $this->postJson('api/v1/login-silence', [
            'code' => $this->code
        ]);

        // 断言是否返回了符合预期的数据键
        $response->assertStatus(200)->assertJsonStructure(['openid', 'isBound']);

        // 断言 isBound 是否正确
        $user = $this->user->where('id', $wechatUser->user_id)->first();
        $this->assertEquals($user->phone, $response->json('isBound') ? $user->phone : null);

        // 是否有重复创建 WechatUser
        $wechatUser = $this->wechatUser->where('openid', 'test_open_id')->get();
        $this->assertCount(1, $wechatUser);

        // 是否有重复创建 User
        $user = $this->user->where('id', $wechatUser->first()->user_id)->get();
        $this->assertCount(1, $user);
    }

    // 测试未绑定手机号用户的登录
    // 预期更新手机号后，返回登录态 token 和用户信息
    public function test_mini_login_on_bound_new_user()
    {
        $user = $this->user->create([
            'name' => '微信测试用户',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '123123',
            'phone' => null
        ]);

        $wechatUser = $this->wechatUser->create([
            'user_id' => $user->id,
            'openid' => 'test_open_id',
            'session_key' => 'test_session_key'
        ]);

        $response = $this->postJson('api/v1/login-bound', [
            'code' => $this->code,
            'openid' => encrypt($wechatUser->openid)
        ]);

        // 断言此用户的手机号码有没有成功更新
        $this->assertDatabaseHas('users', ['id' => $response->json('user.id'), 'phone' => '13800138000']);
        
        // 断言是否返回了符合预期的数据键
        $response->assertStatus(200)->assertJsonStructure(['user', 'token']);
    }

    // 测试已经存在且绑定了手机号的用户登录
    // 预期返回登录态 token & 用户信息
    public function test_mini_login()
    {
        // 创建绑定了手机号的用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '123123',
            'phone' => '13800138000'
        ]);

        $wechatUser = $this->wechatUser->create([
            'user_id' => $user->id,
            'openid' => 'test_open_id',
            'session_key' => 'test_session_key'
        ]);

        // 传入 openid
        $response = $this->postJson('api/v1/login', [
            'openid' => encrypt($wechatUser->openid)
        ]);

        // 断言是否返回了符合预期的数据键
        $response->assertStatus(200)->assertJsonStructure(['user', 'token']);
    }
}
