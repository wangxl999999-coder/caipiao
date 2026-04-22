<?php
/**
 * 应用配置文件
 */

return [
    'name' => '福彩助手API',
    'version' => '1.0.0',
    
    'jwt' => [
        'secret' => 'caipiao_jwt_secret_2024_very_long_key',
        'expire' => 86400 * 7,
    ],
    
    'wechat' => [
        'miniapp' => [
            'app_id' => 'your_app_id',
            'app_secret' => 'your_app_secret',
        ],
    ],
    
    'pagination' => [
        'default_page_size' => 15,
        'max_page_size' => 100,
    ],
    
    'lottery_types' => [
        'ssq' => ['name' => '双色球', 'color' => '#e60012'],
        'qcl' => ['name' => '七乐彩', 'color' => '#1890ff'],
        '22x5' => ['name' => '22选5', 'color' => '#52c41a'],
        '3d' => ['name' => '3D', 'color' => '#faad14'],
        'kl8' => ['name' => '快乐8', 'color' => '#722ed1'],
    ],
];
