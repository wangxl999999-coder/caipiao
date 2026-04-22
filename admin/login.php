<?php
/**
 * 管理后台登录页面
 */

require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(getInput('username'));
    $password = trim(getInput('password'));
    
    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        $db = getDatabase();
        
        $admin = $db->fetch(
            'SELECT * FROM admins WHERE username = :username AND status = 1',
            [':username' => $username]
        );
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin'] = $admin;
            
            $db->update(
                'admins',
                [
                    'last_login_time' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ],
                'id = :id',
                [':id' => $admin['id']]
            );
            
            redirect('index.php');
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台登录 - 福彩助手</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #e60012 0%, #ff4d4f 50%, #ff7875 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 50px;
            width: 100%;
            max-width: 420px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header .logo {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .login-header .title {
            font-size: 28px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 8px;
        }
        
        .login-header .subtitle {
            font-size: 14px;
            color: #999999;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            color: #666666;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #e60012;
            box-shadow: 0 0 0 3px rgba(230, 0, 18, 0.1);
        }
        
        .form-input::placeholder {
            color: #cccccc;
        }
        
        .error-message {
            background: #fff1f0;
            border: 1px solid #ffa39e;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            color: #cf1322;
            font-size: 14px;
        }
        
        .login-btn {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #e60012 0%, #ff4d4f 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230, 0, 18, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🎱</div>
            <div class="title">福彩助手</div>
            <div class="subtitle">管理后台登录</div>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">用户名</label>
                <input type="text" name="username" class="form-input" placeholder="请输入用户名" value="<?php echo escape(getInput('username')); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">密码</label>
                <input type="password" name="password" class="form-input" placeholder="请输入密码" required>
            </div>
            
            <button type="submit" class="login-btn">登 录</button>
        </form>
        
        <div class="login-footer">
            福彩助手管理后台 v1.0.0
        </div>
    </div>
</body>
</html>
