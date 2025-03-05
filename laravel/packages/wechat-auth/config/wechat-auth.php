<?php

return [
    'mini_program' => [
        // AppID
        'app_id'  => env('WECHAT_AUTH_MINI_PROGRAM_APP_ID', ''),         
        // AppSecret
        'secret'  => env('WECHAT_AUTH_MINI_PROGRAM_SECRET', ''),     
        // Token
        'token'   => env('WECHAT_AUTH_MINI_PROGRAM_TOKEN', ''),          
        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
        'aes_key' => env('WECHAT_AUTH_MINI_PROGRAM_AES_KEY', ''),  
    ]
];