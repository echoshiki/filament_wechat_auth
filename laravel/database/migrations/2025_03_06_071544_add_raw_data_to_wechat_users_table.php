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
        Schema::table('wechat_users', function (Blueprint $table) {
            // 增添微信原始数据字段
            $table->json('raw_data')->nullable()->comment('原始用户数据');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wechat_users', function (Blueprint $table) {
            $table->dropColumn('raw_data');
        });
    }
};
