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
    }
    

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 当微信用户不存在时
     */
    public function test_handle_login_when_wechat_user_not_exist()
    {
        // 准备测试数据
        $code = 'test_code';
        $sessionData = [
            'openid' => 'test_openid',
            'session_key' => 'test_session_key',
            'raw_data' => [
                'openid' => 'test_openid',
                'session_key' => 'test_session_key'
            ],
        ];


        // 模拟 getSession 方法
        $this->wechatServiceMock
            ->shouldReceive('getSession')
            ->with($code)
            ->once()
            ->andReturn($sessionData);

        // 执行方法
        $user = $this->userAuthService->handleLogin($code);

        // 验证结果
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['openid' => $sessionData['openid']]);
        $this->assertDatabaseHas('wechat_users', ['openid' => $sessionData['openid']]);

        // 验证返回的用户对象
        $this->assertEquals($sessionData['openid'], $user->openid);
        $this->assertEquals($sessionData['session_key'], $user->session_key);
    }

}
