<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wechat_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('openid')->unique();
            $table->string('unionid')->unique()->nullable();
            $table->string('session_key')->nullable();
            
            // 微信特有属性
            $table->json('raw_data')->nullable()->comment('原始微信数据');
            $table->string('nickname')->nullable();
            $table->string('avatar_url')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();

            $table->softDeletes();
            $table->timestamps();
            
            // 定义外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wechat_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::dropIfExists('wechat_users');
    }
};
