<?php
/**
 * 新闻资讯管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'news';
$pageTitle = '新闻资讯管理';

$typeLabels = [
    1 => '新闻资讯',
    2 => '中奖信息',
];

$page = getPage();
$pageSize = getPageSize();
$type = getInput('type', '');

$where = '1=1';
$params = [];

if ($type !== '') {
    $where .= ' AND type = :type';
    $params[':type'] = (int)$type;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM news WHERE {$where}", $params);
$totalPages = ceil($total / $pageSize);
$offset = ($page - 1) * $pageSize;

$newsList = $db->fetchAll(
    "SELECT n.id, n.type, n.title, n.summary, n.cover_image, n.source, n.author, n.is_banner, n.is_top, n.view_count, n.sort, n.status, n.publish_time, n.created_at 
     FROM news n 
     WHERE {$where} 
     ORDER BY n.is_top DESC, n.publish_time DESC, n.id DESC 
     LIMIT {$limit} OFFSET {$offset}",
    array_merge($params, [':limit' => $pageSize, ':offset' => $offset])
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'save') {
        $id = (int)getInput('id');
        $data = [
            'type' => (int)getInput('type', 1),
            'title' => getInput('title'),
            'summary' => getInput('summary'),
            'content' => getInput('content'),
            'cover_image' => getInput('cover_image'),
            'source' => getInput('source'),
            'author' => getInput('author'),
            'is_banner' => (int)getInput('is_banner', 0),
            'is_top' => (int)getInput('is_top', 0),
            'sort' => (int)getInput('sort', 0),
            'status' => (int)getInput('status', 1),
            'publish_time' => getInput('publish_time') ?: date('Y-m-d H:i:s'),
        ];
        
        if ($id) {
            $db->update('news', $data, 'id = :id', [':id' => $id]);
            jsonResponse(true, '更新成功');
        } else {
            $newId = $db->insert('news', $data);
            jsonResponse(true, '添加成功', ['id' => $newId]);
        }
    }
    
    if ($action === 'delete') {
        $id = (int)getInput('id');
        $db->delete('news', 'id = :id', [':id' => $id]);
        jsonResponse(true, '删除成功');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">新闻资讯管理</span>
</div>

<div class="card">
    <div class="page-header">
        <h3 class="page-title" style="font-size: 18px; margin-bottom: 0;">新闻列表</h3>
        <button type="button" class="btn btn-primary" onclick="openModal('editModal')">添加新闻</button>
    </div>

    <div class="search-box">
        <form method="GET" style="display: flex; gap: 12px; flex: 1;">
            <select name="type" class="form-input" style="width: 150px;">
                <option value="">全部类型</option>
                <?php foreach ($typeLabels as $key => $label): ?>
                <option value="<?php echo escape($key); ?>" <?php echo (string)$type === (string)$key ? 'selected' : ''; ?>><?php echo escape($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">筛选</button>
            <?php if ($type !== ''): ?>
            <a href="news.php" class="btn btn-secondary">清除</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>类型</th>
                    <th>标题</th>
                    <th>来源</th>
                    <th>浏览量</th>
                    <th>轮播</th>
                    <th>置顶</th>
                    <th>状态</th>
                    <th>发布时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($newsList): ?>
                <?php foreach ($newsList as $news): ?>
                <tr>
                    <td><?php echo escape($news['id']); ?></td>
                    <td>
                        <span class="status-tag <?php echo $news['type'] == 2 ? 'success' : 'warning'; ?>">
                            <?php echo escape($typeLabels[$news['type']] ?? '未知'); ?>
                        </span>
                    </td>
                    <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo escape($news['title']); ?>">
                        <?php echo escape($news['title']); ?>
                    </td>
                    <td><?php echo escape($news['source'] ?: '-'); ?></td>
                    <td><?php echo number_format($news['view_count']); ?></td>
                    <td>
                        <?php if ($news['is_banner']): ?>
                        <span class="status-tag success">是</span>
                        <?php else: ?>
                        <span class="status-tag warning">否</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($news['is_top']): ?>
                        <span class="status-tag success">是</span>
                        <?php else: ?>
                        <span class="status-tag warning">否</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-tag <?php echo $news['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?php echo $news['status'] == 1 ? '已发布' : '草稿'; ?>
                        </span>
                    </td>
                    <td><?php echo escape(formatDateShort($news['publish_time'])); ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editNews(<?php echo htmlspecialchars(json_encode($news), ENT_QUOTES); ?>)">编辑</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteNews(<?php echo escape($news['id']); ?>)">删除</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <div class="icon">📰</div>
                            <div>暂无新闻数据</div>
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
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">添加新闻</span>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="newsForm" class="modal-body">
            <input type="hidden" name="id" id="newsId">
            <input type="hidden" name="action" value="save">
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">类型 <span class="required">*</span></label>
                    <select name="type" id="newsType" class="form-input" required>
                        <?php foreach ($typeLabels as $key => $label): ?>
                        <option value="<?php echo escape($key); ?>"><?php echo escape($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="newsStatus" class="form-input">
                        <option value="1">已发布</option>
                        <option value="0">草稿</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">标题 <span class="required">*</span></label>
                <input type="text" name="title" id="newsTitle" class="form-input" placeholder="请输入新闻标题" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">摘要</label>
                <textarea name="summary" id="newsSummary" class="form-input" placeholder="请输入新闻摘要" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">内容 <span class="required">*</span></label>
                <textarea name="content" id="newsContent" class="form-input" placeholder="请输入新闻内容" rows="6" required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">封面图片URL</label>
                    <input type="text" name="cover_image" id="newsCover" class="form-input" placeholder="图片URL">
                </div>
                
                <div class="form-group">
                    <label class="form-label">来源</label>
                    <input type="text" name="source" id="newsSource" class="form-input" placeholder="例如：官方发布">
                </div>
                
                <div class="form-group">
                    <label class="form-label">作者</label>
                    <input type="text" name="author" id="newsAuthor" class="form-input" placeholder="作者名称">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort" id="newsSort" class="form-input" placeholder="数字越小越靠前" value="0">
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; padding-top: 24px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_banner" id="newsBanner" value="1" style="margin-right: 8px;">
                        设为轮播
                    </label>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; padding-top: 24px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_top" id="newsTop" value="1" style="margin-right: 8px;">
                        置顶显示
                    </label>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">取消</button>
            <button type="button" class="btn btn-primary" onclick="saveNews()">保存</button>
        </div>
    </div>
</div>

<script>
function editNews(data) {
    document.getElementById('modalTitle').textContent = '编辑新闻';
    document.getElementById('newsId').value = data.id;
    document.getElementById('newsType').value = data.type;
    document.getElementById('newsTitle').value = data.title || '';
    document.getElementById('newsSummary').value = data.summary || '';
    document.getElementById('newsContent').value = data.content || '';
    document.getElementById('newsCover').value = data.cover_image || '';
    document.getElementById('newsSource').value = data.source || '';
    document.getElementById('newsAuthor').value = data.author || '';
    document.getElementById('newsSort').value = data.sort || 0;
    document.getElementById('newsBanner').checked = data.is_banner == 1;
    document.getElementById('newsTop').checked = data.is_top == 1;
    document.getElementById('newsStatus').value = data.status;
    
    openModal('editModal');
}

function saveNews() {
    var form = document.getElementById('newsForm');
    var formData = new FormData(form);
    
    fetch('news.php', {
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

function deleteNews(id) {
    if (confirm('确定要删除这条新闻吗？')) {
        fetch('news.php', {
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
