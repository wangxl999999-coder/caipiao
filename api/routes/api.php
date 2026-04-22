<?php
/**
 * API 路由配置
 */

use Core\Router;

Router::get('/', function () {
    echo json_encode([
        'code' => 200,
        'message' => '福彩助手API',
        'data' => [
            'name' => '福彩助手API',
            'version' => '1.0.0',
            'timestamp' => time(),
        ],
    ], JSON_UNESCAPED_UNICODE);
});

// 用户相关路由
Router::post('/user/login', 'UserController@login');
Router::post('/user/register', 'UserController@register');
Router::get('/user/info', 'UserController@info', ['Auth']);
Router::put('/user/info', 'UserController@updateInfo', ['Auth']);
Router::post('/user/logout', 'UserController@logout', ['Auth']);

// 开奖相关路由
Router::get('/lottery/list', 'LotteryController@getList');
Router::get('/lottery/latest', 'LotteryController@getLatest');
Router::get('/lottery/detail/{id}', 'LotteryController@getDetail');
Router::get('/lottery/byTypeAndIssue', 'LotteryController@getByTypeAndIssue');

// 站点相关路由
Router::get('/station/list', 'StationController@getList');
Router::get('/station/detail/{id}', 'StationController@getDetail');

// 规则相关路由
Router::get('/rule/list', 'RuleController@getList');
Router::get('/rule/detail/{type}', 'RuleController@getDetail');

// 新闻相关路由
Router::get('/news/list', 'NewsController@getList');
Router::get('/news/banner', 'NewsController@getBanner');
Router::get('/news/detail/{id}', 'NewsController@getDetail');

// 设置相关路由
Router::get('/setting/about-us', 'SettingController@getAboutUs');
Router::get('/setting/user-agreement', 'SettingController@getUserAgreement');
Router::get('/setting/customer-service', 'SettingController@getCustomerService');

// 聊天相关路由
Router::get('/chat/history', 'ChatController@getHistory', ['Auth']);
Router::post('/chat/send', 'ChatController@sendMessage', ['Auth']);
