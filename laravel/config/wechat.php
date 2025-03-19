<?php

return [
    'mini' => [
        'app_id' => env('WECHAT_MINI_APP_ID'),
        'secret' => env('WECHAT_MINI_APP_SECRET'),
        'token' => env('WECHAT_MINI_APP_TOKEN'),
        'aes_key' => env('WECHAT_MINI_APP_AES_KEY'),
        'use_stable_access_token' => false,
        'http' => [
            'throw'  => true, // 状态码非 200、300 时是否抛出异常，默认为开启
            'timeout' => 5.0,
            'retry' => true, // 使用默认重试配置
        ],
    ],
];