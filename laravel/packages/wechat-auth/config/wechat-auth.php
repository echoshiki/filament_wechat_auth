<?php 

return [
    'token'          => 'test',
    'appid'          => 'wx60a43dd8161666d4',
    'appsecret'      => '71308e96a204296c57d7cd4b21b883e8',
    'encodingaeskey' => 'BJIUzE0gqlWy0GxfPp4J1oPTBmOrNDIGPNav1YFH5Z5',
    // 配置商户支付参数（可选，在使用支付功能时需要）
    'mch_id'         => "1235704602",
    'mch_key'        => 'IKI4kpHjU94ji3oqre5zYaQMwLHuZPmj',
    // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
    'ssl_key'        => '',
    'ssl_cer'        => '',
    // 配置单个 P12 格式的支付证书文件路径（可选，适用于退款|打款|红包等场景）
    'ssl_p12'        => '',
    // 缓存目录配置（可选，需拥有读写权限）
    'cache_path'     => '',
];