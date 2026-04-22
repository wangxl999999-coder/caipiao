<?php
/**
 * 站点管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'stations';
$pageTitle = '站点管理';

$page = getPage();
$pageSize = getPageSize();
$keyword = trim(getInput('keyword', ''));

$where = '1=1';
$params = [];

if ($keyword) {
    $where .= ' AND (name LIKE :keyword OR station_no LIKE :keyword OR address LIKE :keyword)';
    $params[':keyword'] = "%{$keyword}%";
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM stations WHERE {$where}", $params);
$totalPages = ceil($total / $pageSize);
$offset = ($page - 1) * $pageSize;

$stations = $db->fetchAll(
    "SELECT s.id, s.name, s.station_no, s.province, s.city, s.district, s.address, s.phone, s.latitude, s.longitude, s.status, s.created_at 
     FROM stations s 
     WHERE {$where} 
     ORDER BY s.created_at DESC 
     LIMIT {$limit} OFFSET {$offset}",
    array_merge($params, [':limit' => $pageSize, ':offset' => $offset])
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'save') {
        $id = (int)getInput('id');
        $data = [
            'name' => getInput('name'),
            'station_no' => getInput('station_no'),
            'province' => getInput('province'),
            'city' => getInput('city'),
            'district' => getInput('district'),
            'address' => getInput('address'),
            'phone' => getInput('phone'),
            'latitude' => getInput('latitude') ?: null,
            'longitude' => getInput('longitude') ?: null,
            'status' => (int)getInput('status', 1),
        ];
        
        if ($id) {
            $db->update('stations', $data, 'id = :id', [':id' => $id]);
            jsonResponse(true, '更新成功');
        } else {
            $newId = $db->insert('stations', $data);
            jsonResponse(true, '添加成功', ['id' => $newId]);
        }
    }
    
    if ($action === 'delete') {
        $id = (int)getInput('id');
        $db->delete('stations', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">站点管理</span>
</div>

<div class="card">
    <div class="page-header">
        <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">站点列表</h3>
        <button type="button" class="btn btn-primary" onclick="openModal('editModal')">添加站点</button>
    </div>

    <div class="search-box">
        <form method="GET" style="display: flex; gap: 12px; flex: 1;">
            <input type="text" name="keyword" class="form-input search-input" placeholder="搜索站点名称/编号/地址" value="<?php echo escape($keyword); ?>">
            <button type="submit" class="btn btn-primary">搜索</button>
            <?php if ($keyword): ?>
            <a href="stations.php" class="btn btn-secondary">清除</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>站点名称</th>
                    <th>站点编号</th>
                    <th>所在城市</th>
                    <th>详细地址</th>
                    <th>联系电话</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stations): ?>
                <?php foreach ($stations as $station): ?>
                <tr>
                    <td><?php echo escape($station['name']); ?></td>
                    <td><?php echo escape($station['station_no']); ?></td>
                    <td>
                        <?php 
                        $location = [];
                        if ($station['province']) $location[] = $station['province'];
                        if ($station['city']) $location[] = $station['city'];
                        if ($station['district']) $location[] = $station['district'];
                        echo escape(implode(' ', $location) ?: '-');
                        ?>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo escape($station['address']); ?>">
                        <?php echo escape($station['address']); ?>
                    </td>
                    <td><?php echo escape($station['phone'] ?: '-'); ?></td>
                    <td>
                        <span class="status-tag <?php echo $station['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?php echo $station['status'] == 1 ? '正常' : '禁用'; ?>
                        </span>
                    </td>
                    <td><?php echo escape(formatDateShort($station['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editStation(<?php echo htmlspecialchars(json_encode($station), ENT_QUOTES); ?>)">编辑</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteStation(<?php echo escape($station['id']); ?>)">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="icon">📍</div>
                            <div>暂无站点数据</div>
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

<!-- 编辑弹窗 -->
<div class="modal-overlay" id="editModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">添加站点</span>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="stationForm" class="modal-body">
            <input type="hidden" name="id" id="stationId">
            <input type="hidden" name="action" value="save">
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">站点名称 <span class="required">*</span></label>
                    <input type="text" name="name" id="stationName" class="form-input" placeholder="例如：中国福利彩票第12345站" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">站点编号 <span class="required">*</span></label>
                    <input type="text" name="station_no" id="stationNo" class="form-input" placeholder="例如：12345" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">省份</label>
                    <input type="text" name="province" id="stationProvince" class="form-input" placeholder="例如：北京市">
                </div>
                
                <div class="form-group">
                    <label class="form-label">城市</label>
                    <input type="text" name="city" id="stationCity" class="form-input" placeholder="例如：北京市">
                </div>
                
                <div class="form-group">
                    <label class="form-label">区县</label>
                    <input type="text" name="district" id="stationDistrict" class="form-input" placeholder="例如：朝阳区">
                </div>
                
                <div class="form-group">
                    <label class="form-label">联系电话</label>
                    <input type="text" name="phone" id="stationPhone" class="form-input" placeholder="例如：010-12345678">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">详细地址 <span class="required">*</span></label>
                <input type="text" name="address" id="stationAddress" class="form-input" placeholder="例如：北京市朝阳区建国路88号" required>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">纬度</label>
                    <input type="text" name="latitude" id="stationLatitude" class="form-input" placeholder="例如：39.9042">
                </div>
                
                <div class="form-group">
                    <label class="form-label">经度</label>
                    <input type="text" name="longitude" id="stationLongitude" class="form-input" placeholder="例如：116.4074">
                </div>
                
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="stationStatus" class="form-input">
                        <option value="1">正常</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">取消</button>
            <button type="button" class="btn btn-primary" onclick="saveStation()">保存</button>
        </div>
    </div>
</div>

<script>
function editStation(data) {
    document.getElementById('modalTitle').textContent = '编辑站点';
    document.getElementById('stationId').value = data.id;
    document.getElementById('stationName').value = data.name || '';
    document.getElementById('stationNo').value = data.station_no || '';
    document.getElementById('stationProvince').value = data.province || '';
    document.getElementById('stationCity').value = data.city || '';
    document.getElementById('stationDistrict').value = data.district || '';
    document.getElementById('stationAddress').value = data.address || '';
    document.getElementById('stationPhone').value = data.phone || '';
    document.getElementById('stationLatitude').value = data.latitude || '';
    document.getElementById('stationLongitude').value = data.longitude || '';
    document.getElementById('stationStatus').value = data.status || 1;
    
    openModal('editModal');
}

function saveStation() {
    var form = document.getElementById('stationForm');
    var formData = new FormData(form);
    
    fetch('stations.php', {
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

function deleteStation(id) {
    if (confirm('确定要删除这个站点吗？')) {
        fetch('stations.php', {
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
