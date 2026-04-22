<?php
/**
 * 规则管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'rules';
$pageTitle = '规则管理';

$typeInfo = [
    'ssq' => ['name' => '双色球', 'color' => '#e60012'],
    'qcl' => ['name' => '七乐彩', 'color' => '#1890ff'],
    '22x5' => ['name' => '22选5', 'color' => '#52c41a'],
    '3d' => ['name' => '3D', 'color' => '#faad14'],
    'kl8' => ['name' => '快乐8', 'color' => '#722ed1'],
];

$rules = $db->fetchAll(
    'SELECT * FROM rules ORDER BY sort ASC, id ASC'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'save') {
        $id = (int)getInput('id');
        $type = getInput('type');
        $data = [
            'type' => $type,
            'name' => $typeInfo[$type]['name'] ?? '',
            'draw_time' => getInput('draw_time'),
            'draw_channel' => getInput('draw_channel'),
            'rules' => getInput('rules'),
            'prize_description' => getInput('prize_description'),
            'bet_rules' => getInput('bet_rules'),
            'sort' => (int)getInput('sort', 0),
            'status' => (int)getInput('status', 1),
        ];
        
        if ($id) {
            $db->update('rules', $data, 'id = :id', [':id' => $id]);
            jsonResponse(true, '更新成功');
        } else {
            $newId = $db->insert('rules', $data);
            jsonResponse(true, '添加成功', ['id' => $newId]);
        }
    }
    
    if ($action === 'delete') {
        $id = (int)getInput('id');
        $db->delete('rules', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">规则管理</span>
</div>

<div class="card">
    <div class="page-header">
        <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">规则列表</h3>
        <button type="button" class="btn btn-primary" onclick="openModal('editModal')">添加规则</button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>彩种</th>
                    <th>开奖时间</th>
                    <th>开奖栏目</th>
                    <th>排序</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rules): ?>
                <?php foreach ($rules as $rule): ?>
                <tr>
                    <td>
                        <span style="color: <?php echo $typeInfo[$rule['type']]['color'] ?? '#333'; ?>; font-weight: 500;">
                            <?php echo escape($rule['name']); ?>
                        </span>
                    </td>
                    <td><?php echo escape($rule['draw_time'] ?: '-'); ?></td>
                    <td><?php echo escape($rule['draw_channel'] ?: '-'); ?></td>
                    <td><?php echo escape($rule['sort']); ?></td>
                    <td>
                        <span class="status-tag <?php echo $rule['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?php echo $rule['status'] == 1 ? '启用' : '禁用'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editRule(<?php echo htmlspecialchars(json_encode($rule), ENT_QUOTES); ?>)">编辑</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRule(<?php echo escape($rule['id']); ?>)">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="icon">📋</div>
                            <div>暂无规则数据</div>
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
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">添加规则</span>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="ruleForm" class="modal-body">
            <input type="hidden" name="id" id="ruleId">
            <input type="hidden" name="action" value="save">
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">彩种 <span class="required">*</span></label>
                    <select name="type" id="ruleType" class="form-input" required>
                        <option value="">请选择彩种</option>
                        <?php foreach ($typeInfo as $key => $info): ?>
                        <option value="<?php echo escape($key); ?>"><?php echo escape($info['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">开奖时间</label>
                    <input type="text" name="draw_time" id="ruleDrawTime" class="form-input" placeholder="例如：每周二、四、日 21:15">
                </div>
                
                <div class="form-group">
                    <label class="form-label">开奖栏目</label>
                    <input type="text" name="draw_channel" id="ruleDrawChannel" class="form-input" placeholder="例如：中国教育电视台">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">游戏规则</label>
                <textarea name="rules" id="ruleRules" class="form-input" placeholder="请输入游戏规则说明" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">奖级说明</label>
                <textarea name="prize_description" id="rulePrize" class="form-input" placeholder="请输入奖级说明" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">投注规则</label>
                <textarea name="bet_rules" id="ruleBetRules" class="form-input" placeholder="请输入投注规则" rows="4"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort" id="ruleSort" class="form-input" placeholder="数字越小越靠前" value="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="ruleStatus" class="form-input">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">取消</button>
            <button type="button" class="btn btn-primary" onclick="saveRule()">保存</button>
        </div>
    </div>
</div>

<script>
function editRule(data) {
    document.getElementById('modalTitle').textContent = '编辑规则';
    document.getElementById('ruleId').value = data.id;
    document.getElementById('ruleType').value = data.type;
    document.getElementById('ruleDrawTime').value = data.draw_time || '';
    document.getElementById('ruleDrawChannel').value = data.draw_channel || '';
    document.getElementById('ruleRules').value = data.rules || '';
    document.getElementById('rulePrize').value = data.prize_description || '';
    document.getElementById('ruleBetRules').value = data.bet_rules || '';
    document.getElementById('ruleSort').value = data.sort || 0;
    document.getElementById('ruleStatus').value = data.status;
    
    openModal('editModal');
}

function saveRule() {
    var form = document.getElementById('ruleForm');
    var formData = new FormData(form);
    
    fetch('rules.php', {
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

function deleteRule(id) {
    if (confirm('确定要删除这条规则吗？')) {
        fetch('rules.php', {
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
