<?php
/**
 * 用户控制器
 */

namespace Controllers;

use Core\Controller;
use Core\Jwt;
use Models\UserModel;
use Exception;

class UserController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function login()
    {
        try {
            $code = $this->post('code');
            $phone = $this->post('phone');
            $loginType = $this->post('loginType', 'phone');
            
            if ($loginType === 'wechat' && $code) {
                return $this->wechatLogin($code);
            }
            
            if ($phone) {
                return $this->phoneLogin($phone);
            }
            
            return $this->error('请提供登录信息', 400);
        } catch (Exception $e) {
            return $this->error('登录失败: ' . $e->getMessage(), 500);
        }
    }

    private function phoneLogin($phone)
    {
        $user = $this->userModel->findByPhone($phone);
        
        if (!$user) {
            $userId = $this->userModel->create([
                'phone' => $phone,
                'nickname' => '彩民' . substr($phone, -4),
                'status' => 1,
            ]);
            $user = $this->userModel->getById($userId);
        } elseif ($user['status'] != 1) {
            return $this->error('账号已被禁用', 403);
        }
        
        $this->userModel->updateLoginInfo($user['id'], $this->getClientIP());
        
        $token = Jwt::encode([
            'user_id' => $user['id'],
            'phone' => $user['phone'],
        ]);
        
        unset($user['openid']);
        unset($user['unionid']);
        
        return $this->success([
            'token' => $token,
            'userInfo' => $user,
        ], '登录成功');
    }

    private function wechatLogin($code)
    {
        $config = require __DIR__ . '/../config/app.php';
        $appId = $config['wechat']['miniapp']['app_id'];
        $appSecret = $config['wechat']['miniapp']['app_secret'];
        
        $userInfo = $this->post('userInfo');
        
        $mockOpenid = 'mock_openid_' . md5($code . time());
        
        $user = $this->userModel->findByOpenid($mockOpenid);
        
        if (!$user) {
            $data = [
                'openid' => $mockOpenid,
                'status' => 1,
            ];
            
            if ($userInfo && is_array($userInfo)) {
                $data['nickname'] = $userInfo['nickName'] ?? '';
                $data['avatar'] = $userInfo['avatarUrl'] ?? '';
                $data['gender'] = $userInfo['gender'] ?? 0;
                $data['city'] = $userInfo['city'] ?? '';
                $data['province'] = $userInfo['province'] ?? '';
                $data['country'] = $userInfo['country'] ?? '';
            }
            
            $userId = $this->userModel->create($data);
            $user = $this->userModel->getById($userId);
        } elseif ($user['status'] != 1) {
            return $this->error('账号已被禁用', 403);
        }
        
        $this->userModel->updateLoginInfo($user['id'], $this->getClientIP());
        
        $token = Jwt::encode([
            'user_id' => $user['id'],
            'openid' => $user['openid'],
        ]);
        
        unset($user['openid']);
        unset($user['unionid']);
        
        return $this->success([
            'token' => $token,
            'userInfo' => $user,
        ], '登录成功');
    }

    public function register()
    {
        try {
            $action = $this->post('action');
            
            if ($action === 'sendCode') {
                return $this->sendCode();
            }
            
            $phone = $this->post('phone');
            $code = $this->post('code');
            
            $this->validate([
                'phone' => 'required|phone',
                'code' => 'required|min:4|max:6',
            ], $this->post());
            
            $exists = $this->userModel->findByPhone($phone);
            if ($exists) {
                return $this->error('手机号已注册', 400);
            }
            
            $userId = $this->userModel->create([
                'phone' => $phone,
                'nickname' => '彩民' . substr($phone, -4),
                'status' => 1,
            ]);
            
            return $this->success([
                'user_id' => $userId,
            ], '注册成功');
        } catch (Exception $e) {
            return $this->error('注册失败: ' . $e->getMessage(), 500);
        }
    }

    private function sendCode()
    {
        $phone = $this->post('phone');
        
        $this->validate([
            'phone' => 'required|phone',
        ], $this->post());
        
        $mockCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        return $this->success([
            'code' => $mockCode,
            'expire' => 300,
        ], '验证码已发送');
    }

    public function info()
    {
        global $current_user;
        
        if (!$current_user) {
            return $this->unauthorized();
        }
        
        $user = $this->userModel->getById($current_user['id']);
        
        if (!$user) {
            return $this->unauthorized();
        }
        
        unset($user['openid']);
        unset($user['unionid']);
        
        return $this->success($user);
    }

    public function updateInfo()
    {
        global $current_user;
        
        if (!$current_user) {
            return $this->unauthorized();
        }
        
        $data = [];
        
        $nickname = $this->post('nickname');
        if ($nickname !== null) {
            $data['nickname'] = $nickname;
        }
        
        $avatar = $this->post('avatar');
        if ($avatar !== null) {
            $data['avatar'] = $avatar;
        }
        
        $gender = $this->post('gender');
        if ($gender !== null) {
            $data['gender'] = (int)$gender;
        }
        
        if (empty($data)) {
            return $this->error('没有需要更新的信息', 400);
        }
        
        $result = $this->userModel->update($current_user['id'], $data);
        
        if ($result) {
            $user = $this->userModel->getById($current_user['id']);
            unset($user['openid']);
            unset($user['unionid']);
            
            return $this->success($user, '更新成功');
        }
        
        return $this->error('更新失败', 500);
    }

    public function logout()
    {
        global $current_user;
        
        return $this->success(null, '已退出登录');
    }

    private function getClientIP()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }
        
        return $ip;
    }
}
