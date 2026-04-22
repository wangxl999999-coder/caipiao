<?php
/**
 * 在线客服管理页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'chat';
$pageTitle = '在线客服';

$conversations = $db->fetchAll(
    "SELECT 
        cm.*,
        u.nickname,
        u.avatar,
        u.phone,
        (SELECT COUNT(*) FROM chat_messages WHERE user_id = cm.user_id AND from_type = 1 AND is_read = 0) as unread_count
    FROM chat_messages cm
    INNER JOIN (
        SELECT 
            user_id,
            MAX(created_at) as last_time
        FROM chat_messages
        GROUP BY user_id
    ) last_msg ON cm.user_id = last_msg.user_id AND cm.created_at = last_msg.last_time
    LEFT JOIN users u ON cm.user_id = u.id
    ORDER BY cm.created_at DESC"
);

$currentUserId = getInput('user_id');
$messages = [];
$currentUser = null;

if ($currentUserId) {
    $currentUser = $db->fetch(
        'SELECT id, nickname, avatar, phone, created_at FROM users WHERE id = :id',
        [':id' => $currentUserId]
    );
    
    $messages = $db->fetchAll(
        "SELECT * FROM chat_messages WHERE user_id = :user_id ORDER BY created_at ASC",
        [':user_id' => $currentUserId]
    );
    
    $db->query(
        "UPDATE chat_messages SET is_read = 1 WHERE user_id = :user_id AND from_type = 1",
        [':user_id' => $currentUserId]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'send') {
        $userId = (int)getInput('user_id');
        $content = getInput('content');
        
        if ($userId && $content) {
            $db->insert('chat_messages', [
                'user_id' => $userId,
                'from_type' => 2,
                'type' => 'text',
                'content' => $content,
                'is_read' => 0,
            ]);
            jsonResponse(true, '发送成功');
        }
        jsonResponse(false, '参数错误');
    }
}

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">在线客服</span>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="display: flex; height: 600px;">
        <!-- 会话列表 -->
        <div style="width: 280px; border-right: 1px solid #e8e8e8; display: flex; flex-direction: column;">
            <div style="padding: 16px; border-bottom: 1px solid #e8e8e8; background: #fafafa;">
                <h3 style="font-size: 16px; margin-bottom: 0;">会话列表</h3>
            </div>
            <div style="flex: 1; overflow-y: auto;">
                <?php if ($conversations): ?>
                <?php foreach ($conversations as $conv): ?>
                <a href="chat.php?user_id=<?php echo escape($conv['user_id']); ?>" 
                   style="display: flex; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; text-decoration: none; color: inherit; transition: background 0.3s; <?php echo $conv['user_id'] == $currentUserId ? 'background: #fff1f0;' : ''; ?>"
                   class="conversation-item">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; overflow: hidden;">
                        <?php if ($conv['avatar']): ?>
                        <img src="<?php echo escape($conv['avatar']); ?>" style="width: 100%; height: 100%;">
                        <?php else: ?>
                        👤
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo escape($conv['nickname'] ?: '用户' . $conv['user_id']); ?>
                            </span>
                            <span style="font-size: 12px; color: #999; flex-shrink: 0; margin-left: 8px;">
                                <?php echo escape(date('H:i', strtotime($conv['created_at']))); ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: #999; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo escape($conv['from_type'] == 1 ? '用户: ' : '客服: '); ?>
                                <?php echo escape(mb_strlen($conv['content']) > 20 ? mb_substr($conv['content'], 0, 20) . '...' : $conv['content']); ?>
                            </span>
                            <?php if ($conv['unread_count'] > 0): ?>
                            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 6px; background: #e60012; color: #fff; border-radius: 9px; font-size: 12px; flex-shrink: 0; margin-left: 8px;">
                                <?php echo escape($conv['unread_count']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state" style="padding-top: 80px;">
                    <div class="icon">💬</div>
                    <div>暂无非会话</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 聊天区域 -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <?php if ($currentUser): ?>
            <div style="padding: 16px 20px; border-bottom: 1px solid #e8e8e8; display: flex; align-items: center;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-right: 12px; overflow: hidden;">
                    <?php if ($currentUser['avatar']): ?>
                    <img src="<?php echo escape($currentUser['avatar']); ?>" style="width: 100%; height: 100%;">
                    <?php else: ?>
                    👤
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-weight: 500;"><?php echo escape($currentUser['nickname'] ?: '用户' . $currentUser['id']); ?></div>
                    <div style="font-size: 12px; color: #999;"><?php echo escape($currentUser['phone'] ?: ''); ?></div>
                </div>
            </div>
            
            <div id="messageList" style="flex: 1; overflow-y: auto; padding: 16px 20px; background: #fafafa;">
                <?php if ($messages): ?>
                <?php foreach ($messages as $msg): ?>
                <div style="display: flex; margin-bottom: 16px; <?php echo $msg['from_type'] == 2 ? 'justify-content: flex-end;' : ''; ?>">
                    <?php if ($msg['from_type'] == 1): ?>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0;">
                        👤
                    </div>
                    <?php endif; ?>
                    <div style="max-width: 60%;">
                        <div style="padding: 10px 14px; border-radius: 8px; <?php echo $msg['from_type'] == 2 ? 'background: linear-gradient(135deg, #e60012 0%, #ff4d4f 100%); color: #fff;' : 'background: #fff; color: #333; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'; ?>">
                            <?php echo nl2br(escape($msg['content'])); ?>
                        </div>
                        <div style="font-size: 12px; color: #999; margin-top: 4px; <?php echo $msg['from_type'] == 2 ? 'text-align: right;' : ''; ?>">
                            <?php echo escape(formatDate($msg['created_at'])); ?>
                        </div>
                    </div>
                    <?php if ($msg['from_type'] == 2): ?>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e60012; display: flex; align-items: center; justify-content: center; margin-left: 10px; flex-shrink: 0; color: #fff; font-size: 14px;">
                        客服
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state" style="padding-top: 120px;">
                    <div class="icon">💬</div>
                    <div>暂无聊天记录</div>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="padding: 16px 20px; border-top: 1px solid #e8e8e8; background: #fff;">
                <form id="chatForm" style="display: flex; gap: 12px;">
                    <input type="hidden" name="action" value="send">
                    <input type="hidden" name="user_id" value="<?php echo escape($currentUserId); ?>">
                    <input type="text" name="content" id="chatInput" class="form-input" style="flex: 1;" placeholder="输入消息...">
                    <button type="button" class="btn btn-primary" onclick="sendMessage()">发送</button>
                </form>
            </div>
            <?php else: ?>
            <div class="empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div class="icon">💬</div>
                <div style="font-size: 16px; color: #666; margin-bottom: 8px;">选择一个会话开始聊天</div>
                <div style="font-size: 13px; color: #999;">点击左侧会话列表中的用户进行聊天</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
var messageList = document.getElementById('messageList');
if (messageList) {
    messageList.scrollTop = messageList.scrollHeight;
}

function sendMessage() {
    var input = document.getElementById('chatInput');
    var content = input.value.trim();
    
    if (!content) {
        return;
    }
    
    var form = document.getElementById('chatForm');
    var formData = new FormData(form);
    
    fetch('chat.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            input.value = '';
            location.reload();
        } else {
            alert(res.message);
        }
    });
}

document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>

<?php
require_once __DIR__ . '/views/footer.php';
