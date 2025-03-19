<?php

namespace Tests\Unit\Services;

use App\Services\Interfaces\WechatServiceInterface;
use App\Services\UserAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WechatUser;
use Mockery;

class UserAuthServiceTest extends TestCase
{
    
    use RefreshDatabase;
    
    private $wechatServiceMock;
    private $userAuthService;
    private $user;
    private $wechatUser;
    
    // 公共测试数据
    private $code;
    private $sessionData;
    private $phoneData;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建模拟对象，其实就是小程序服务类（统一接口）
        $this->wechatServiceMock = Mockery::mock(WechatServiceInterface::class);
        
        // 创建真实模型实例
        $this->user = new User();
        $this->wechatUser = new WechatUser();

        // 创建服务实例（测试对象）并注入模拟对象（依赖注入）
        $this->userAuthService = new UserAuthService(
            $this->wechatServiceMock,
            $this->user,
            $this->wechatUser
        );

        // 公共测试数据
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
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 场景：当微信用户不存在时（关联用户自然也不存在）
     */
    public function test_handle_login_when_wechat_user_not_exist()
    {
        // 模拟 getSession 方法
        $this->wechatServiceMock
            ->shouldReceive('getSession')
            ->with($this->code)
            ->once()
            ->andReturn($this->sessionData);

        // 执行方法
        $wechatUser = $this->userAuthService->handleLoginInSilence($this->code);

        // 断言 $user 变量是 WechatUser 类的实例
        $this->assertInstanceOf(WechatUser::class, $wechatUser);
        $this->assertInstanceOf(User::class, $wechatUser->user);
        
        // 断言数据库中存在对应的微信用户和关联的用户
        $this->assertDatabaseHas('wechat_users', ['openid' => $this->sessionData['openid']]);
        $this->assertDatabaseHas('users', ['id' => $wechatUser->user_id]);
    }

    /**
     * 场景：当微信用户存在但未关联用户时，创建并关联新用户
     */
    public function test_handle_login_when_wechat_user_exist_but_not_related()
    {
        // 模拟 getSession 方法
        $this->wechatServiceMock
            ->shouldReceive('getSession')
            ->with($this->code)
            ->once()
            ->andReturn($this->sessionData);

        // 创建一个微信用户
        $this->wechatUser->create([
            'user_id' => null,
            'openid' => $this->sessionData['openid'],
            'session_key' => $this->sessionData['session_key']
        ]);

        // 执行方法
        $wechatUser = $this->userAuthService->handleLoginInSilence($this->code);

        // 断言 $user 变量是 WechatUser 类的实例
        $this->assertInstanceOf(WechatUser::class, $wechatUser);
        $this->assertInstanceOf(User::class, $wechatUser->user);
        
        // 断言数据库中存在对应的微信用户和关联的用户
        $this->assertDatabaseHas('wechat_users', ['openid' => $this->sessionData['openid']]);
        $this->assertDatabaseHas('users', ['id' => $wechatUser->user_id]);
    }

    /**·
     * 场景：当微信用户存在且已关联用户时，直接返回用户
     */
    public function test_handle_login_when_wechat_user_exist_and_related()
    {
        // 模拟 getSession 方法
        $this->wechatServiceMock
            ->shouldReceive('getSession')
            ->with($this->code)
            ->once()
            ->andReturn($this->sessionData);

        // 创建一个新用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'password' => '123123',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);    

        // 创建一个微信用户并关联上面的用户
        $wechatUser = $this->wechatUser->create([
            'user_id' => $user->id,
            'openid' => $this->sessionData['openid'],
            'session_key' => $this->sessionData['session_key']
        ]);

        // 执行方法
        $wechatUser = $this->userAuthService->handleLoginInSilence($this->code);

        // 断言 $user 变量是 WechatUser 类的实例
        $this->assertInstanceOf(WechatUser::class, $wechatUser);
        $this->assertInstanceOf(User::class, $wechatUser->user);
        
        // 断言数据库中存在对应的微信用户和关联的用户
        $this->assertDatabaseHas('wechat_users', ['openid' => $this->sessionData['openid']]);
        $this->assertDatabaseHas('users', ['id' => $wechatUser->user_id]);
    }

    /**
     * 场景：绑定手机号
     */
    public function test_handle_bind_phone_number()
    {
        // 模拟 getPhoneNumber 方法
        $this->wechatServiceMock
            ->shouldReceive('getPhoneNumber')
            ->with($this->code)
            ->once()
            ->andReturn($this->phoneData);

        // 创建一个没有电话号码的测试用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'password' => '123123',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        // 执行方法
        $user = $this->userAuthService->bindPhoneNumber($this->code, $user);

        // 断言 $user 变量是 User 类的实例
        $this->assertInstanceOf(User::class, $user);
        
        // 断言数据库中存在对应的用户记录
        $this->assertDatabaseHas('users', ['phone' => $this->phoneData['phone_info']['phoneNumber']]);
        
        // 验证返回的用户对象与传入的原始参数是否一致
        $this->assertEquals($this->phoneData['phone_info']['phoneNumber'], $user->phone);
        $this->assertNotNull($user->phone_verified_at);
    }

    /**
     * 场景：生成 token
     */
    public function test_generate_token()
    {
        // 创建一个测试用户
        $user = $this->user->create([
            'name' => '微信测试用户',
            'password' => '123123',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        // 执行方法
        $token = $this->userAuthService->generateToken($user);

        // 断言 token 不为空
        $this->assertNotEmpty($token);

        // 断言 token 可以正确解析
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

}
