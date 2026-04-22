<?php
/**
 * 开奖管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'lotteries';
$pageTitle = '开奖管理';

$typeInfo = [
    'ssq' => ['name' => '双色球', 'color' => '#e60012'],
    'qcl' => ['name' => '七乐彩', 'color' => '#1890ff'],
    '22x5' => ['name' => '22选5', 'color' => '#52c41a'],
    '3d' => ['name' => '3D', 'color' => '#faad14'],
    'kl8' => ['name' => '快乐8', 'color' => '#722ed1'],
];

$page = getPage();
$pageSize = getPageSize();
$type = getInput('type', '');

$where = '1=1';
$params = [];

if ($type) {
    $where .= ' AND type = :type';
    $params[':type'] = $type;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM lotteries WHERE {$where}", $params);
$totalPages = ceil($total / $pageSize);
$offset = ($page - 1) * $pageSize;

$lotteries = $db->fetchAll(
    "SELECT l.id, l.type, l.type_name, l.issue, l.red_balls, l.blue_balls, l.issue_date, l.draw_time, l.sales_amount, l.jackpot_amount, l.prize_info, l.status 
     FROM lotteries l 
     WHERE {$where} 
     ORDER BY l.issue_date DESC, l.issue DESC 
     LIMIT {$limit} OFFSET {$offset}",
    array_merge($params, [':limit' => $pageSize, ':offset' => $offset])
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'save') {
        $id = (int)getInput('id');
        $data = [
            'type' => getInput('type'),
            'type_name' => $typeInfo[getInput('type')]['name'] ?? '',
            'issue' => getInput('issue'),
            'red_balls' => json_encode(explode(',', getInput('red_balls')), JSON_UNESCAPED_UNICODE),
            'blue_balls' => json_encode(array_filter(explode(',', getInput('blue_balls'))), JSON_UNESCAPED_UNICODE),
            'issue_date' => getInput('issue_date'),
            'sales_amount' => getInput('sales_amount') ?: null,
            'jackpot_amount' => getInput('jackpot_amount') ?: null,
            'status' => (int)getInput('status', 1),
        ];
        
        if ($id) {
            $db->update('lotteries', $data, 'id = :id', [':id' => $id]);
            jsonResponse(true, '更新成功');
        } else {
            $newId = $db->insert('lotteries', $data);
            jsonResponse(true, '添加成功', ['id' => $newId]);
        }
    }
    
    if ($action === 'delete') {
        $id = (int)getInput('id');
        $db->delete('lotteries', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">开奖管理</span>
</div>

<div class="card">
    <div class="page-header">
        <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">开奖列表</h3>
        <button type="button" class="btn btn-primary" onclick="openModal('editModal')">添加开奖</button>
    </div>

    <div class="search-box">
        <form method="GET" style="display: flex; gap: 12px; flex: 1;">
            <select name="type" class="form-input" style="width: 150px;">
                <option value="">全部彩种</option>
                <?php foreach ($typeInfo as $key => $info): ?>
                <option value="<?php echo escape($key); ?>" <?php echo $type == $key ? 'selected' : ''; ?>><?php echo escape($info['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">筛选</button>
            <?php if ($type): ?>
            <a href="lotteries.php" class="btn btn-secondary">清除</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>彩种</th>
                    <th>期号</th>
                    <th>开奖号码</th>
                    <th>开奖日期</th>
                    <th>销售总额</th>
                    <th>奖池金额</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lotteries): ?>
                <?php foreach ($lotteries as $lottery): 
                    $redBalls = json_decode($lottery['red_balls'], true) ?: [];
                    $blueBalls = json_decode($lottery['blue_balls'], true) ?: [];
                ?>
                <tr>
                    <td>
                        <span style="color: <?php echo $typeInfo[$lottery['type']]['color'] ?? '#333'; ?>; font-weight: 500;">
                            <?php echo escape($lottery['type_name']); ?>
                        </span>
                    </td>
                    <td>第<?php echo escape($lottery['issue']); ?>期</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                            <?php foreach ($redBalls as $ball): ?>
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #e60012 0%, #ff4d4f 100%); color: #fff; font-size: 12px; font-weight: 600;">
                                <?php echo escape($ball); ?>
                            </span>
                            <?php endforeach; ?>
                            <?php if ($blueBalls): ?>
                            <span style="margin: 0 4px;">|</span>
                            <?php foreach ($blueBalls as $ball): ?>
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%); color: #fff; font-size: 12px; font-weight: 600;">
                                <?php echo escape($ball); ?>
                            </span>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo escape($lottery['issue_date']); ?></td>
                    <td><?php echo $lottery['sales_amount'] ? '¥' . number_format($lottery['sales_amount']) : '-'; ?></td>
                    <td><?php echo $lottery['jackpot_amount'] ? '¥' . number_format($lottery['jackpot_amount']) : '-'; ?></td>
                    <td>
                        <span class="status-tag <?php echo $lottery['status'] == 1 ? 'success' : 'warning'; ?>">
                            <?php echo $lottery['status'] == 1 ? '已开奖' : '待开奖'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editLottery(<?php echo htmlspecialchars(json_encode($lottery), ENT_QUOTES); ?>)">编辑</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteLottery(<?php echo escape($lottery['id']); ?>)">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8">
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

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">共 <?php echo $total; ?> 条，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 页</div>
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>&type=<?php echo urlencode($type); ?>" class="pagination-btn">上一页</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?php echo $i; ?>&type=<?php echo urlencode($type); ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>&type=<?php echo urlencode($type); ?>" class="pagination-btn">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- 编辑弹窗 -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">添加开奖</span>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="lotteryForm" class="modal-body">
            <input type="hidden" name="id" id="lotteryId">
            <input type="hidden" name="action" value="save">
            
            <div class="form-group">
                <label class="form-label">彩种 <span class="required">*</span></label>
                <select name="type" id="lotteryType" class="form-input" required>
                    <option value="">请选择彩种</option>
                    <?php foreach ($typeInfo as $key => $info): ?>
                    <option value="<?php echo escape($key); ?>"><?php echo escape($info['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">期号 <span class="required">*</span></label>
                <input type="text" name="issue" id="lotteryIssue" class="form-input" placeholder="例如：2024001" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">开奖日期 <span class="required">*</span></label>
                <input type="date" name="issue_date" id="lotteryDate" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">红球号码 <span class="required">*</span> (逗号分隔)</label>
                <input type="text" name="red_balls" id="lotteryRedBalls" class="form-input" placeholder="例如：01,05,12,18,25,30" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">蓝球号码 (逗号分隔，没有则留空)</label>
                <input type="text" name="blue_balls" id="lotteryBlueBalls" class="form-input" placeholder="例如：08">
            </div>
            
            <div class="form-group">
                <label class="form-label">销售总额 (元)</label>
                <input type="number" name="sales_amount" id="lotterySales" class="form-input" placeholder="例如：350000000">
            </div>
            
            <div class="form-group">
                <label class="form-label">奖池金额 (元)</label>
                <input type="number" name="jackpot_amount" id="lotteryJackpot" class="form-input" placeholder="例如：2200000000">
            </div>
            
            <div class="form-group">
                <label class="form-label">状态</label>
                <select name="status" id="lotteryStatus" class="form-input">
                    <option value="1">已开奖</option>
                    <option value="0">待开奖</option>
                </select>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">取消</button>
            <button type="button" class="btn btn-primary" onclick="saveLottery()">保存</button>
        </div>
    </div>
</div>

<script>
function editLottery(data) {
    document.getElementById('modalTitle').textContent = '编辑开奖';
    document.getElementById('lotteryId').value = data.id;
    document.getElementById('lotteryType').value = data.type;
    document.getElementById('lotteryIssue').value = data.issue;
    document.getElementById('lotteryDate').value = data.issue_date;
    
    var redBalls = JSON.parse(data.red_balls || '[]');
    var blueBalls = JSON.parse(data.blue_balls || '[]');
    document.getElementById('lotteryRedBalls').value = redBalls.join(',');
    document.getElementById('lotteryBlueBalls').value = blueBalls.join(',');
    
    document.getElementById('lotterySales').value = data.sales_amount || '';
    document.getElementById('lotteryJackpot').value = data.jackpot_amount || '';
    document.getElementById('lotteryStatus').value = data.status;
    
    openModal('editModal');
}

function saveLottery() {
    var form = document.getElementById('lotteryForm');
    var formData = new FormData(form);
    
    fetch('lotteries.php', {
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

function deleteLottery(id) {
    if (confirm('确定要删除这条开奖记录吗？')) {
        fetch('lotteries.php', {
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
