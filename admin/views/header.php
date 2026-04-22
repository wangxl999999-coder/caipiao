<?php
if (!defined('ADMIN_ROOT')) {
    exit;
}
$currentAdmin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>福彩助手管理后台</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #e60012;
            --primary-hover: #cf1322;
            --primary-light: #fff1f0;
            --text-primary: #333333;
            --text-secondary: #666666;
            --text-muted: #999999;
            --border-color: #e8e8e8;
            --bg-color: #f5f5f5;
            --white: #ffffff;
            --success-color: #52c41a;
            --warning-color: #faad14;
            --danger-color: #ff4d4f;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-size: 14px;
        }
        
        /* 顶部导航栏 */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(135deg, #e60012 0%, #ff4d4f 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        .header-logo {
            font-size: 28px;
            margin-right: 12px;
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--white);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            color: var(--white);
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        
        .admin-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .admin-name {
            font-size: 14px;
        }
        
        .logout-btn {
            padding: 6px 16px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            color: var(--white);
            text-decoration: none;
            font-size: 13px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }
        
        /* 侧边栏 */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 200px;
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 900;
        }
        
        .menu-group {
            padding: 12px 0;
        }
        
        .menu-title {
            padding: 12px 20px;
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .menu-item.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 500;
        }
        
        .menu-icon {
            font-size: 18px;
            margin-right: 12px;
        }
        
        /* 主内容区 */
        .main-content {
            margin-left: 200px;
            margin-top: 60px;
            padding: 24px;
            min-height: calc(100vh - 60px);
        }
        
        /* 面包屑导航 */
        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb .separator {
            margin: 0 8px;
            color: var(--text-muted);
        }
        
        .breadcrumb .current {
            color: var(--text-primary);
        }
        
        /* 页面标题 */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* 卡片 */
        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }
        
        /* 按钮 */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            padding: 0 16px;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #e60012 0%, #ff4d4f 100%);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(230, 0, 18, 0.25);
        }
        
        .btn-secondary {
            background-color: var(--bg-color);
            color: var(--text-secondary);
        }
        
        .btn-secondary:hover {
            background-color: #e8e8e8;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: var(--white);
        }
        
        .btn-success:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: var(--white);
        }
        
        .btn-danger:hover {
            opacity: 0.9;
        }
        
        .btn-sm {
            height: 28px;
            padding: 0 12px;
            font-size: 13px;
        }
        
        /* 表格 */
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: #fafafa;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 13px;
            white-space: nowrap;
        }
        
        td {
            color: var(--text-primary);
            font-size: 14px;
        }
        
        tr:hover td {
            background-color: #fafafa;
        }
        
        /* 状态标签 */
        .status-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .status-tag.success {
            background-color: rgba(82, 196, 26, 0.1);
            color: var(--success-color);
        }
        
        .status-tag.danger {
            background-color: rgba(255, 77, 79, 0.1);
            color: var(--danger-color);
        }
        
        .status-tag.warning {
            background-color: rgba(250, 173, 20, 0.1);
            color: var(--warning-color);
        }
        
        /* 操作按钮组 */
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .actions .btn {
            padding: 4px 12px;
            height: auto;
            font-size: 12px;
        }
        
        /* 表单 */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .form-label .required {
            color: var(--danger-color);
            margin-left: 4px;
        }
        
        .form-input {
            width: 100%;
            height: 36px;
            padding: 0 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(230, 0, 18, 0.1);
        }
        
        textarea.form-input {
            height: auto;
            min-height: 100px;
            padding: 12px;
            resize: vertical;
        }
        
        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        
        /* 分页 */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 8px;
        }
        
        .pagination-info {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .pagination-btn {
            min-width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .pagination-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .pagination-btn.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* 搜索框 */
        .search-box {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            max-width: 300px;
        }
        
        /* 统计卡片 */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        /* 空状态 */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        /* 提示消息 */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            color: var(--success-color);
        }
        
        .alert-error {
            background-color: #fff2f0;
            border: 1px solid #ffccc7;
            color: var(--danger-color);
        }
        
        /* 弹窗遮罩 */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .modal-overlay.show {
            display: flex;
        }
        
        .modal {
            background-color: var(--white);
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-title {
            font-size: 16px;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <header class="header">
        <div class="header-left">
            <span class="header-logo">🎱</span>
            <span class="header-title">福彩助手管理后台</span>
        </div>
        <div class="header-right">
            <div class="admin-info">
                <div class="admin-avatar">👤</div>
                <span class="admin-name"><?php echo escape($currentAdmin['nickname'] ?? $currentAdmin['username']); ?></span>
            </div>
            <a href="logout.php" class="logout-btn">退出登录</a>
        </div>
    </header>
    
    <!-- 侧边栏 -->
    <aside class="sidebar">
        <div class="menu-group">
            <div class="menu-title">系统管理</div>
            <a href="index.php" class="menu-item <?php echo ($currentMenu ?? '') == 'dashboard' ? 'active' : ''; ?>">
                <span class="menu-icon">📊</span>
                <span>控制台</span>
            </a>
        </div>
        
        <div class="menu-group">
            <div class="menu-title">用户管理</div>
            <a href="users.php" class="menu-item <?php echo ($currentMenu ?? '') == 'users' ? 'active' : ''; ?>">
                <span class="menu-icon">👥</span>
                <span>用户列表</span>
            </a>
        </div>
        
        <div class="menu-group">
            <div class="menu-title">内容管理</div>
            <a href="lotteries.php" class="menu-item <?php echo ($currentMenu ?? '') == 'lotteries' ? 'active' : ''; ?>">
                <span class="menu-icon">🎱</span>
                <span>开奖管理</span>
            </a>
            <a href="stations.php" class="menu-item <?php echo ($currentMenu ?? '') == 'stations' ? 'active' : ''; ?>">
                <span class="menu-icon">📍</span>
                <span>站点管理</span>
            </a>
            <a href="news.php" class="menu-item <?php echo ($currentMenu ?? '') == 'news' ? 'active' : ''; ?>">
                <span class="menu-icon">📰</span>
                <span>新闻资讯</span>
            </a>
            <a href="rules.php" class="menu-item <?php echo ($currentMenu ?? '') == 'rules' ? 'active' : ''; ?>">
                <span class="menu-icon">📋</span>
                <span>规则管理</span>
            </a>
        </div>
        
        <div class="menu-group">
            <div class="menu-title">客服管理</div>
            <a href="chat.php" class="menu-item <?php echo ($currentMenu ?? '') == 'chat' ? 'active' : ''; ?>">
                <span class="menu-icon">💬</span>
                <span>在线客服</span>
            </a>
        </div>
        
        <div class="menu-group">
            <div class="menu-title">系统设置</div>
            <a href="settings.php" class="menu-item <?php echo ($currentMenu ?? '') == 'settings' ? 'active' : ''; ?>">
                <span class="menu-icon">⚙️</span>
                <span>系统配置</span>
            </a>
            <a href="admins.php" class="menu-item <?php echo ($currentMenu ?? '') == 'admins' ? 'active' : ''; ?>">
                <span class="menu-icon">🔐</span>
                <span>管理员</span>
            </a>
        </div>
    </aside>
    
    <!-- 主内容区 -->
    <main class="main-content">
