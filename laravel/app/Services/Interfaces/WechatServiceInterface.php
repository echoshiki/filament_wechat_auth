<?php

namespace App\Services\Interfaces;

interface WechatServiceInterface
{
    public function getSession(string $code): array;

    public function getPhoneNumber(string $code): array;
}