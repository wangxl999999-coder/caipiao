<?php
/**
 * 管理员管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'admins';
$pageTitle = '管理员管理';

$currentAdmin = getCurrentAdmin();

$admins = $db->fetchAll(
    'SELECT id, username, nickname, status, last_login_time, last_login_ip, created_at FROM admins ORDER BY created_at DESC'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'save') {
        $id = (int)getInput('id');
        $username = getInput('username');
        $nickname = getInput('nickname');
        $password = getInput('password');
        $status = (int)getInput('status', 1);
        
        if ($id) {
            $data = [
                'nickname' => $nickname,
                'status' => $status,
            ];
            
            if ($password) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $db->update('admins', $data, 'id = :id', [':id' => $id]);
            jsonResponse(true, '更新成功');
        } else {
            if (!$password) {
                jsonResponse(false, '请输入密码');
            }
            
            $existing = $db->fetch('SELECT id FROM admins WHERE username = :username', [':username' => $username]);
            if ($existing) {
                jsonResponse(false, '用户名已存在');
            }
            
            $db->insert('admins', [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'nickname' => $nickname ?: $username,
                'status' => $status,
            ]);
            jsonResponse(true, '添加成功');
        }
    }
    
    if ($action === 'delete') {
        $id = (int)getInput('id');
        
        if ($id == $currentAdmin['id']) {
            jsonResponse(false, '不能删除自己');
        }
        
        $db->delete('admins', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">管理员管理</span>
</div>

<div class="card">
    <div class="page-header">
        <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">管理员列表</h3>
        <button type="button" class="btn btn-primary" onclick="openModal('editModal')">添加管理员</button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>昵称</th>
                    <th>状态</th>
                    <th>最后登录时间</th>
                    <th>最后登录IP</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admins): ?>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo escape($admin['id']); ?></td>
                    <td><?php echo escape($admin['username']); ?></td>
                    <td><?php echo escape($admin['nickname']); ?></td>
                    <td>
                        <span class="status-tag <?php echo $admin['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?php echo $admin['status'] == 1 ? '正常' : '禁用'; ?>
                        </span>
                    </td>
                    <td><?php echo escape($admin['last_login_time'] ? formatDate($admin['last_login_time']) : '-'); ?></td>
                    <td><?php echo escape($admin['last_login_ip'] ?: '-'); ?></td>
                    <td><?php echo escape(formatDate($admin['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editAdmin(<?php echo htmlspecialchars(json_encode($admin), ENT_QUOTES); ?>)">编辑</button>
                            <?php if ($admin['id'] != $currentAdmin['id']): ?>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo escape($admin['id']); ?>)">删除</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="icon">🔐</div>
                            <div>暂无管理员数据</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 编辑弹窗 -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">添加管理员</span>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="adminForm" class="modal-body">
            <input type="hidden" name="id" id="adminId">
            <input type="hidden" name="action" value="save">
            
            <div class="form-group" id="usernameField">
                <label class="form-label">用户名 <span class="required">*</span></label>
                <input type="text" name="username" id="adminUsername" class="form-input" placeholder="请输入用户名" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">昵称</label>
                <input type="text" name="nickname" id="adminNickname" class="form-input" placeholder="请输入昵称">
            </div>
            
            <div class="form-group">
                <label class="form-label" id="passwordLabel">密码 <span class="required">*</span></label>
                <input type="password" name="password" id="adminPassword" class="form-input" placeholder="请输入密码">
                <p style="font-size: 12px; color: #999; margin-top: 4px;">编辑时留空则不修改密码</p>
            </div>
            
            <div class="form-group">
                <label class="form-label">状态</label>
                <select name="status" id="adminStatus" class="form-input">
                    <option value="1">正常</option>
                    <option value="0">禁用</option>
                </select>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">取消</button>
            <button type="button" class="btn btn-primary" onclick="saveAdmin()">保存</button>
        </div>
    </div>
</div>

<script>
function editAdmin(data) {
    document.getElementById('modalTitle').textContent = '编辑管理员';
    document.getElementById('adminId').value = data.id;
    document.getElementById('adminUsername').value = data.username || '';
    document.getElementById('adminUsername').readOnly = true;
    document.getElementById('adminUsername').style.backgroundColor = '#f5f5f5';
    document.getElementById('adminNickname').value = data.nickname || '';
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminStatus').value = data.status;
    
    document.getElementById('passwordLabel').innerHTML = '密码';
    
    openModal('editModal');
}

document.querySelector('[onclick="openModal(\'editModal\')"]')?.addEventListener('click', function() {
    document.getElementById('modalTitle').textContent = '添加管理员';
    document.getElementById('adminId').value = '';
    document.getElementById('adminUsername').value = '';
    document.getElementById('adminUsername').readOnly = false;
    document.getElementById('adminUsername').style.backgroundColor = '#fff';
    document.getElementById('adminNickname').value = '';
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminStatus').value = 1;
    document.getElementById('passwordLabel').innerHTML = '密码 <span class="required">*</span>';
});

function saveAdmin() {
    var form = document.getElementById('adminForm');
    var formData = new FormData(form);
    
    fetch('admins.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert(res.message);
        }
    });
}

function deleteAdmin(id) {
    if (confirm('确定要删除这个管理员吗？')) {
        fetch('admins.php', {
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
