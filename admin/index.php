<?php
/**
 * 管理后台控制台首页
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'dashboard';
$pageTitle = '控制台';

$totalUsers = $db->fetchColumn('SELECT COUNT(*) FROM users');
$totalLotteries = $db->fetchColumn('SELECT COUNT(*) FROM lotteries');
$totalStations = $db->fetchColumn('SELECT COUNT(*) FROM stations WHERE status = 1');
$totalNews = $db->fetchColumn('SELECT COUNT(*) FROM news WHERE status = 1');

$recentUsers = $db->fetchAll(
    'SELECT id, phone, nickname, avatar, status, created_at FROM users ORDER BY created_at DESC LIMIT 5'
);

$recentLotteries = $db->fetchAll(
    'SELECT l.id, l.type, l.type_name, l.issue, l.issue_date, l.red_balls, l.blue_balls, l.status 
     FROM lotteries l 
     ORDER BY l.issue_date DESC, l.issue DESC LIMIT 5'
);

$unreadChats = $db->fetchColumn(
    'SELECT COUNT(*) FROM chat_messages WHERE from_type = 1 AND is_read = 0'
);

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">控制台</span>
</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
        <div class="stat-label">注册用户</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($totalLotteries); ?></div>
        <div class="stat-label">开奖期数</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($totalStations); ?></div>
        <div class="stat-label">彩票站点</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format($totalNews); ?></div>
        <div class="stat-label">新闻资讯</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
    <div class="card">
        <div class="page-header" style="margin-bottom: 20px;">
            <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">最新注册用户</h3>
            <a href="users.php" class="btn btn-sm btn-secondary">查看全部</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>用户</th>
                        <th>手机号</th>
                        <th>状态</th>
                        <th>注册时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentUsers): ?>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                    <?php echo $user['avatar'] ? '<img src="' . escape($user['avatar']) . '" style="width: 32px; height: 32px; border-radius: 50%;">' : '👤'; ?>
                                </div>
                                <?php echo escape($user['nickname'] ?: '彩民用户'); ?>
                            </div>
                        </td>
                        <td><?php echo $user['phone'] ? escape(substr($user['phone'], 0, 3) . '****' . substr($user['phone'], -4)) : '-'; ?></td>
                        <td>
                            <span class="status-tag <?php echo $user['status'] == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $user['status'] == 1 ? '正常' : '禁用'; ?>
                            </span>
                        </td>
                        <td><?php echo escape(formatDateShort($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="icon">👥</div>
                                <div>暂无用户数据</div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="page-header" style="margin-bottom: 20px;">
            <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">最新开奖信息</h3>
            <a href="lotteries.php" class="btn btn-sm btn-secondary">查看全部</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>彩种</th>
                        <th>期号</th>
                        <th>开奖日期</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentLotteries): ?>
                    <?php foreach ($recentLotteries as $lottery): ?>
                    <tr>
                        <td><?php echo escape($lottery['type_name']); ?></td>
                        <td>第<?php echo escape($lottery['issue']); ?>期</td>
                        <td><?php echo escape($lottery['issue_date']); ?></td>
                        <td>
                            <span class="status-tag <?php echo $lottery['status'] == 1 ? 'success' : 'warning'; ?>">
                                <?php echo $lottery['status'] == 1 ? '已开奖' : '待开奖'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="icon">🎱</div>
                                <div>暂无开奖数据</div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($unreadChats > 0): ?>
<div class="alert alert-success">
    您有 <?php echo $unreadChats; ?> 条未读客服消息，请及时处理。
    <a href="chat.php" style="color: #52c41a; margin-left: 10px;">查看消息</a>
</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/views/footer.php';
