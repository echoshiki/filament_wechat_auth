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

    public function test_mini_login_new_user()
    {
        $response = $this->postJson('api/v1/login', [
            'code' => $this->code
        ]);

        // 断言是否返回 token 是否成功
        $response->assertStatus(200)->assertJsonStructure(['token']);

        $wechatUser = $this->wechatUser->where('openid', $this->sessionData['open_id'])->first();

        // 断言数据库中存在对应的微信用户记录
        $this->assertDatabaseHas('wechat_users', ['openid' => $this->sessionData['open_id']]);

        // 是否存在关联用户
        $this->assertDatabaseHas('users', ['id' => $wechatUser->user_id]);

        // 断言 token 是否正确
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $wechatUser->user_id]);

    }

    public function test_mini_login_exist_user()
    {
        // 创建测试用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '123123'
        ]);

        $wechatUser = $this->wechatUser->create([
            'user_id' => $user->id,
            'openid' => 'test_open_id',
            'session_key' => 'test_session_key'
        ]);

        $response = $this->postJson('api/v1/login', [
            'code' => $this->code
        ]);

        // 断言是否返回 token 是否成功
        $response->assertStatus(200)->assertJsonStructure(['token']);

        // 是否有重复创建
        $count = $this->user->count();
        $this->assertEquals(1, $count);

        $count = $this->wechatUser->count();
        $this->assertEquals(1, $count);
    }

    public function test_bind_without_login()
    {
        $response = $this->postJson('api/v1/bind', [
            'code' => $this->code
        ]);

        // 断言是否失败
        $response->assertStatus(401);
    }

    public function test_mini_bind_phone_number()
    {
        // 创建测试用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '123123',
            'phone' => null,
            'phone_verified_at' => null
        ]);

        // 生成 token
        $token = $user->createToken('mini')->plainTextToken;

        // 创建携带 token 的请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bind', [
            'code' => $this->code
        ]);

        // 断言是否返回 token 是否成功
        $response->assertStatus(200)->assertJsonStructure(['user']);

        // 断言此用户的手机号码有没有成功更新
        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '13800138000']);  
    }
}
