<?php
/**
 * 应用配置文件
 */

return [
    'name' => '福彩助手API',
    'version' => '1.0.0',
    'debug' => true,
    'timezone' => 'Asia/Shanghai',
    
    // JWT配置
    'jwt' => [
        'secret' => 'caipiao_jwt_secret_key_2024',
        'expire' => 7 * 24 * 3600, // 7天过期
    ],
    
    // 微信小程序配置
    'wechat' => [
        'miniapp' => [
            'app_id' => 'your_app_id',
            'app_secret' => 'your_app_secret',
        ],
    ],
    
    // 上传配置
    'upload' => [
        'path' => __DIR__ . '/../public/uploads/',
        'url' => '/uploads/',
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],
    
    // 分页配置
    'pagination' => [
        'default_page_size' => 10,
        'max_page_size' => 100,
    ],
];
