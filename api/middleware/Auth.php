<?php
/**
 * 认证中间件
 */

namespace Middleware;

use Core\Jwt;
use Core\Database;

class Auth
{
    public function handle()
    {
        $authHeader = $this->getAuthorizationHeader();
        
        if (!$authHeader) {
            return [
                'code' => 401,
                'message' => '缺少认证令牌',
                'data' => null,
                'timestamp' => time(),
            ];
        }

        if (strpos($authHeader, 'Bearer ') !== 0) {
            return [
                'code' => 401,
                'message' => '认证令牌格式错误',
                'data' => null,
                'timestamp' => time(),
            ];
        }

        $token = substr($authHeader, 7);
        
        $payload = Jwt::decode($token);
        
        if (!$payload) {
            return [
                'code' => 401,
                'message' => '认证令牌无效或已过期',
                'data' => null,
                'timestamp' => time(),
            ];
        }

        if (!isset($payload['user_id'])) {
            return [
                'code' => 401,
                'message' => '认证令牌无效',
                'data' => null,
                'timestamp' => time(),
            ];
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            'SELECT * FROM users WHERE id = :id AND status = 1',
            [':id' => $payload['user_id']]
        );

        if (!$user) {
            return [
                'code' => 401,
                'message' => '用户不存在或已被禁用',
                'data' => null,
                'timestamp' => time(),
            ];
        }

        $GLOBALS['current_user'] = $user;

        return true;
    }

    private function getAuthorizationHeader()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
        
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        return null;
    }
}
