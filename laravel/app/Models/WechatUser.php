<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatUser extends Model
{
    /** @use HasFactory<\Database\Factories\WechatUserFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'openid', 'unionid', 'session_key', 
        'nickname', 'avatar', 'raw_data', 'user_id'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'session_key' => 'encrypted'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // 访问器：微信头像处理
    public function getAvatarAttribute()
    {
        $avatar = $this->avatar ?? null;
        return $avatar ?: asset('images/wechat-default.png');
    }
}
