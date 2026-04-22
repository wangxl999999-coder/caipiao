<?php
/**
 * 用户管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'users';
$pageTitle = '用户管理';

$page = getPage();
$pageSize = getPageSize();
$keyword = trim(getInput('keyword', ''));

$where = '1=1';
$params = [];

if ($keyword) {
    $where .= ' AND (phone LIKE :keyword OR nickname LIKE :keyword)';
    $params[':keyword'] = "%{$keyword}%";
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$where}", $params);
$totalPages = ceil($total / $pageSize);
$offset = ($page - 1) * $pageSize;

$users = $db->fetchAll(
    "SELECT id, openid, phone, nickname, avatar, gender, city, province, country, status, last_login_time, created_at 
     FROM users 
     WHERE {$where} 
     ORDER BY created_at DESC 
     LIMIT {$limit} OFFSET {$offset}",
    array_merge($params, [':limit' => $pageSize, ':offset' => $offset])
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    $id = (int)getInput('id');
    
    if ($action === 'toggleStatus' && $id) {
        $user = $db->fetch('SELECT status FROM users WHERE id = :id', [':id' => $id]);
        if ($user) {
            $newStatus = $user['status'] == 1 ? 0 : 1;
            $db->update('users', ['status' => $newStatus], 'id = :id', [':id' => $id]);
            jsonResponse(true, '状态更新成功');
        }
        jsonResponse(false, '用户不存在');
    }
    
    if ($action === 'delete' && $id) {
        $db->delete('users', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">用户管理</span>
</div>

<div class="card">
    <div class="search-box">
        <form method="GET" style="display: flex; gap: 12px; flex: 1;">
            <input type="text" name="keyword" class="form-input search-input" placeholder="搜索手机号/昵称" value="<?php echo escape($keyword); ?>">
            <button type="submit" class="btn btn-primary">搜索</button>
            <?php if ($keyword): ?>
            <a href="users.php" class="btn btn-secondary">清除</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户信息</th>
                    <th>手机号</th>
                    <th>地区</th>
                    <th>状态</th>
                    <th>最后登录</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo escape($user['id']); ?></td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-right: 10px; overflow: hidden;">
                                <?php echo $user['avatar'] ? '<img src="' . escape($user['avatar']) . '" style="width: 100%; height: 100%;">' : '👤'; ?>
                            </div>
                            <div>
                                <div style="font-weight: 500;"><?php echo escape($user['nickname'] ?: '彩民用户'); ?></div>
                                <div style="font-size: 12px; color: #999;">
                                    <?php 
                                    if ($user['gender'] == 1) echo '男';
                                    elseif ($user['gender'] == 2) echo '女';
                                    else echo '未知';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo $user['phone'] ? escape($user['phone']) : '-'; ?></td>
                    <td>
                        <?php 
                        $location = [];
                        if ($user['province']) $location[] = $user['province'];
                        if ($user['city']) $location[] = $user['city'];
                        echo escape(implode(' ', $location) ?: '-');
                        ?>
                    </td>
                    <td>
                        <span class="status-tag <?php echo $user['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?php echo $user['status'] == 1 ? '正常' : '禁用'; ?>
                        </span>
                    </td>
                    <td><?php echo escape($user['last_login_time'] ? formatDate($user['last_login_time']) : '-'); ?></td>
                    <td><?php echo escape(formatDate($user['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleStatus(<?php echo escape($user['id']); ?>)">
                                <?php echo $user['status'] == 1 ? '禁用' : '启用'; ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo escape($user['id']); ?>)">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8">
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

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">共 <?php echo $total; ?> 条，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 页</div>
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword); ?>" class="pagination-btn">上一页</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword); ?>" class="pagination-btn">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleStatus(id) {
    if (confirm('确定要修改该用户的状态吗？')) {
        fetch('users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggleStatus&id=' + id
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }
}

function deleteUser(id) {
    if (confirm('确定要删除该用户吗？此操作不可恢复。')) {
        fetch('users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&id=' + id
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }
}
</script>

<?php
require_once __DIR__ . '/views/footer.php';
