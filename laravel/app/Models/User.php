<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 引入 RBAC 权限管理
use Spatie\Permission\Traits\HasRoles;

// WechatUser 模型
use App\Models\WechatUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 关联微信用户
     */
    public function wechat_users() {
        return $this->hasOne(WechatUser::class);
    }

    /**
     * 访问器：优先使用微信昵称
     */
    public function getNicknameAttribute()
    {
        return optional($this->wechat)->nickname ?? $this->name;
    }

    /**
     * 访问器：统一头像获取
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) return $this->avatar;
        // 默认指向 public/images/default-avatar.png
        return optional($this->wechat)->avatar ?? '';
    }

    /**
     * 根据微信 openid 查询关联的用户
     */
    public function scopeWithWechatUserOpenid($query, string $openid)
    {
        return $query->whereHas('wechat_users', function ($q) use ($openid) {
            $q->where('openid', $openid);
        });
    }

    /**
     * 查询活跃用户
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * 查询非活跃用户
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * 查询最近注册的用户
     */
    public function scopeRecentlyRegistered(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * 查询指定角色的用户
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->role($role);
    }

    /**
     * 查询有指定权限的用户
     */
    public function scopeWithPermission(Builder $query, string $permission): Builder
    {
        return $query->permission($permission);
    }

    /**
     * 检查用户是否激活
     */
    public function isActive(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * 检查用户是否管理员
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * 重置密码
     */
    public function resetPassword(string $password): self
    {
        $this->password = Hash::make($password);
        $this->save();
        
        return $this;
    }

    /**
     * 激活用户账号
     */
    public function activate(): self
    {
        if (!$this->isActive()) {
            $this->email_verified_at = Carbon::now();
            $this->save();
        }
        
        return $this;
    }

    /**
     * 停用用户账号
     */
    public function deactivate(): self
    {
        $this->email_verified_at = null;
        $this->save();
        
        return $this;
    }

    /**
     * 更新用户最后登录时间
     */
    public function updateLastLogin(): self
    {
        $this->last_login_at = Carbon::now();
        $this->save();
        
        return $this;
    }

    /**
     * 检查用户是否在线
     */
    public function isOnline(): bool
    {
        return $this->last_login_at !== null && 
               $this->last_login_at->diffInMinutes(Carbon::now()) < 15;
    }

    /**
     * 通过邮箱查找用户(静态方法)
     */
    public static function findByEmail(string $email)
    {
        return static::where('email', $email)->first();
    }

    /**
     * 通过用户名查找用户(静态方法)
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * 通过用户名查找用户(作用域方法)
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }
}
