<?php
/**
 * 系统配置页面
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDatabase();
$currentMenu = 'settings';
$pageTitle = '系统配置';

$settings = [];
$allSettings = $db->fetchAll('SELECT * FROM settings ORDER BY `group` ASC, sort ASC, id ASC');

foreach ($allSettings as $setting) {
    $value = json_decode($setting['value'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $setting['value'] = $value;
    }
    $settings[$setting['key']] = $setting;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getInput('action');
    
    if ($action === 'saveAboutUs') {
        $aboutContent = getInput('about_content');
        
        $existing = $db->fetch('SELECT * FROM settings WHERE `key` = :key', [':key' => 'about_us']);
        $value = json_encode(['content' => $aboutContent], JSON_UNESCAPED_UNICODE);
        
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = :id', [':id' => $existing['id']]);
        } else {
            $db->insert('settings', [
                'key' => 'about_us',
                'value' => $value,
                'name' => '关于我们',
                'description' => '关于我们页面内容',
                'group' => 'content',
                'sort' => 1,
                'status' => 1,
            ]);
        }
        
        jsonResponse(true, '保存成功');
    }
    
    if ($action === 'saveAgreement') {
        $agreementContent = getInput('agreement_content');
        $privacyContent = getInput('privacy_content');
        
        $existing = $db->fetch('SELECT * FROM settings WHERE `key` = :key', [':key' => 'user_agreement']);
        $value = json_encode(['content' => $agreementContent], JSON_UNESCAPED_UNICODE);
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = :id', [':id' => $existing['id']]);
        } else {
            $db->insert('settings', [
                'key' => 'user_agreement',
                'value' => $value,
                'name' => '用户协议',
                'description' => '用户协议页面内容',
                'group' => 'content',
                'sort' => 2,
                'status' => 1,
            ]);
        }
        
        $existing = $db->fetch('SELECT * FROM settings WHERE `key` = :key', [':key' => 'privacy_policy']);
        $value = json_encode(['content' => $privacyContent], JSON_UNESCAPED_UNICODE);
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = :id', [':id' => $existing['id']]);
        } else {
            $db->insert('settings', [
                'key' => 'privacy_policy',
                'value' => $value,
                'name' => '隐私政策',
                'description' => '隐私政策页面内容',
                'group' => 'content',
                'sort' => 3,
                'status' => 1,
            ]);
        }
        
        jsonResponse(true, '保存成功');
    }
    
    if ($action === 'saveCustomerService') {
        $phone = getInput('cs_phone');
        $workTime = getInput('cs_work_time');
        $qq = getInput('cs_qq');
        $welcomeMsg = getInput('cs_welcome_msg');
        
        $existing = $db->fetch('SELECT * FROM settings WHERE `key` = :key', [':key' => 'customer_service']);
        $value = json_encode([
            'phone' => $phone,
            'work_time' => $workTime,
            'qq' => $qq,
            'welcome_msg' => $welcomeMsg,
        ], JSON_UNESCAPED_UNICODE);
        
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = :id', [':id' => $existing['id']]);
        } else {
            $db->insert('settings', [
                'key' => 'customer_service',
                'value' => $value,
                'name' => '客服配置',
                'description' => '在线客服配置',
                'group' => 'content',
                'sort' => 4,
                'status' => 1,
            ]);
        }
        
        jsonResponse(true, '保存成功');
    }
}

$aboutUs = isset($settings['about_us']['value']['content']) ? $settings['about_us']['value']['content'] : '';
$userAgreement = isset($settings['user_agreement']['value']['content']) ? $settings['user_agreement']['value']['content'] : '';
$privacyPolicy = isset($settings['privacy_policy']['value']['content']) ? $settings['privacy_policy']['value']['content'] : '';
$customerService = $settings['customer_service']['value'] ?? [
    'phone' => '400-123-4567',
    'work_time' => '9:00-18:00',
    'qq' => '123456789',
    'welcome_msg' => '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？'
];

require_once __DIR__ . '/views/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">首页</a>
    <span class="separator">/</span>
    <span class="current">系统配置</span>
</div>

<div class="card">
    <div style="border-bottom: 1px solid #e8e8e8; margin-bottom: 24px;">
        <div style="display: flex; gap: 0;">
            <button type="button" class="tab-btn active" data-tab="about">关于我们</button>
            <button type="button" class="tab-btn" data-tab="agreement">用户协议</button>
            <button type="button" class="tab-btn" data-tab="service">客服配置</button>
        </div>
    </div>
    
    <!-- 关于我们 -->
    <div class="tab-content" id="tab-about">
        <form id="aboutForm">
            <input type="hidden" name="action" value="saveAboutUs">
            <div class="form-group">
                <label class="form-label">关于我们内容</label>
                <textarea name="about_content" class="form-input" placeholder="请输入关于我们内容，支持HTML" rows="12"><?php echo escape($aboutUs); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <button type="button" class="btn btn-primary" onclick="saveAboutUs()">保存</button>
            </div>
        </form>
    </div>
    
    <!-- 用户协议 -->
    <div class="tab-content" id="tab-agreement" style="display: none;">
        <form id="agreementForm">
            <input type="hidden" name="action" value="saveAgreement">
            <div class="form-group">
                <label class="form-label">用户协议</label>
                <textarea name="agreement_content" class="form-input" placeholder="请输入用户协议内容，支持HTML" rows="10"><?php echo escape($userAgreement); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">隐私政策</label>
                <textarea name="privacy_content" class="form-input" placeholder="请输入隐私政策内容，支持HTML" rows="10"><?php echo escape($privacyPolicy); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <button type="button" class="btn btn-primary" onclick="saveAgreement()">保存</button>
            </div>
        </form>
    </div>
    
    <!-- 客服配置 -->
    <div class="tab-content" id="tab-service" style="display: none;">
        <form id="serviceForm">
            <input type="hidden" name="action" value="saveCustomerService">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">客服电话</label>
                    <input type="text" name="cs_phone" class="form-input" placeholder="例如：400-123-4567" value="<?php echo escape($customerService['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">工作时间</label>
                    <input type="text" name="cs_work_time" class="form-input" placeholder="例如：9:00-18:00" value="<?php echo escape($customerService['work_time'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">客服QQ</label>
                <input type="text" name="cs_qq" class="form-input" placeholder="例如：123456789" value="<?php echo escape($customerService['qq'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">欢迎语</label>
                <textarea name="cs_welcome_msg" class="form-input" placeholder="智能客服欢迎语" rows="3"><?php echo escape($customerService['welcome_msg'] ?? ''); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <button type="button" class="btn btn-primary" onclick="saveService()">保存</button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    font-size: 14px;
    cursor: pointer;
    color: #666;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all 0.3s;
}

.tab-btn:hover {
    color: #e60012;
}

.tab-btn.active {
    color: #e60012;
    border-bottom-color: #e60012;
    font-weight: 500;
}
</style>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
    });
});

function saveAboutUs() {
    var form = document.getElementById('aboutForm');
    var formData = new FormData(form);
    
    fetch('settings.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
        } else {
            alert(res.message);
        }
    });
}

function saveAgreement() {
    var form = document.getElementById('agreementForm');
    var formData = new FormData(form);
    
    fetch('settings.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
        } else {
            alert(res.message);
        }
    });
}

function saveService() {
    var form = document.getElementById('serviceForm');
    var formData = new FormData(form);
    
    fetch('settings.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
        } else {
            alert(res.message);
        }
    });
}
</script>

<?php
require_once __DIR__ . '/views/footer.php';
