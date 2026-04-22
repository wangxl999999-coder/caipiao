-- 福彩助手数据库初始化脚本
-- 创建时间: 2024-01-01
-- 数据库: caipiao

-- 创建数据库
CREATE DATABASE IF NOT EXISTS caipiao DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE caipiao;

-- ============================================
-- 管理员表
-- ============================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（加密）',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `role` tinyint(1) NOT NULL DEFAULT 1 COMMENT '角色：1-普通管理员，2-超级管理员',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-启用',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- ============================================
-- 用户表
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `openid` varchar(100) DEFAULT NULL COMMENT '微信openid',
  `unionid` varchar(100) DEFAULT NULL COMMENT '微信unionid',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `nickname` varchar(100) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `gender` tinyint(1) DEFAULT 0 COMMENT '性别：0-未知，1-男，2-女',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `country` varchar(50) DEFAULT NULL COMMENT '国家',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-正常',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  UNIQUE KEY `uk_phone` (`phone`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ============================================
-- 彩种类型表
-- ============================================
CREATE TABLE IF NOT EXISTS `lottery_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) NOT NULL COMMENT '彩种标识：ssq-双色球，qcl-七乐彩，22x5-22选5，3d-3D，kl8-快乐8',
  `name` varchar(50) NOT NULL COMMENT '彩种名称',
  `color` varchar(20) NOT NULL DEFAULT '#e60012' COMMENT '展示颜色',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `draw_time` varchar(100) DEFAULT NULL COMMENT '开奖时间',
  `draw_channel` varchar(100) DEFAULT NULL COMMENT '开奖栏目',
  `description` text COMMENT '描述',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type` (`type`),
  KEY `idx_sort` (`sort`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='彩种类型表';

-- ============================================
-- 开奖信息表
-- ============================================
CREATE TABLE IF NOT EXISTS `lotteries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) NOT NULL COMMENT '彩种标识',
  `type_name` varchar(50) NOT NULL COMMENT '彩种名称',
  `issue` varchar(50) NOT NULL COMMENT '期号',
  `red_balls` varchar(255) NOT NULL COMMENT '红球号码（JSON格式数组）',
  `blue_balls` varchar(255) DEFAULT NULL COMMENT '蓝球号码（JSON格式数组）',
  `issue_date` date NOT NULL COMMENT '开奖日期',
  `draw_time` datetime DEFAULT NULL COMMENT '开奖时间',
  `sales_amount` decimal(15,2) DEFAULT NULL COMMENT '销售总额（元）',
  `jackpot_amount` decimal(15,2) DEFAULT NULL COMMENT '奖池金额（元）',
  `prize_info` text COMMENT '中奖信息（JSON格式）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-待开奖，1-已开奖',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_issue` (`type`, `issue`),
  KEY `idx_type` (`type`),
  KEY `idx_issue_date` (`issue_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='开奖信息表';

-- ============================================
-- 站点表
-- ============================================
CREATE TABLE IF NOT EXISTS `stations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `code` varchar(50) NOT NULL COMMENT '站点编号',
  `name` varchar(100) NOT NULL COMMENT '站点名称',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `district` varchar(50) DEFAULT NULL COMMENT '区县',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '纬度',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '经度',
  `phone` varchar(50) DEFAULT NULL COMMENT '联系电话',
  `business_hours` varchar(100) DEFAULT NULL COMMENT '营业时间',
  `description` text COMMENT '描述',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-正常',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_city` (`city`),
  KEY `idx_latitude_longitude` (`latitude`, `longitude`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点表';

-- ============================================
-- 规则表
-- ============================================
CREATE TABLE IF NOT EXISTS `rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) NOT NULL COMMENT '彩种标识',
  `name` varchar(50) NOT NULL COMMENT '彩种名称',
  `draw_time` varchar(255) DEFAULT NULL COMMENT '开奖时间说明',
  `draw_channel` varchar(255) DEFAULT NULL COMMENT '开奖栏目说明',
  `rules` text COMMENT '游戏规则（HTML格式）',
  `prize_description` text COMMENT '奖级说明（HTML格式）',
  `bet_rules` text COMMENT '投注规则说明',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type` (`type`),
  KEY `idx_sort` (`sort`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='规则表';

-- ============================================
-- 新闻资讯表
-- ============================================
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型：1-新闻资讯，2-中奖信息',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `summary` varchar(500) DEFAULT NULL COMMENT '摘要',
  `content` longtext COMMENT '内容（HTML格式）',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `source` varchar(50) DEFAULT NULL COMMENT '来源',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `is_banner` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否轮播：0-否，1-是',
  `is_top` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否置顶：0-否，1-是',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '浏览量',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-草稿，1-发布，2-下架',
  `publish_time` datetime DEFAULT NULL COMMENT '发布时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_banner` (`is_banner`),
  KEY `idx_is_top` (`is_top`),
  KEY `idx_status` (`status`),
  KEY `idx_publish_time` (`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻资讯表';

-- ============================================
-- 系统配置表
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `key` varchar(100) NOT NULL COMMENT '配置键名',
  `value` longtext COMMENT '配置值（JSON格式）',
  `name` varchar(100) NOT NULL COMMENT '配置名称',
  `description` varchar(255) DEFAULT NULL COMMENT '配置描述',
  `group` varchar(50) NOT NULL DEFAULT 'basic' COMMENT '配置分组：basic-基础配置，about-关于我们，agreement-用户协议，service-客服配置',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key`),
  KEY `idx_group` (`group`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ============================================
-- 聊天消息表
-- ============================================
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `from_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '发送方：1-用户，2-客服',
  `type` varchar(20) NOT NULL DEFAULT 'text' COMMENT '消息类型：text-文本，image-图片',
  `content` text NOT NULL COMMENT '消息内容',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已读：0-未读，1-已读',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_from_type` (`from_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天消息表';

-- ============================================
-- 初始化数据
-- ============================================

-- 插入管理员账号（密码：admin123，需要用password_hash加密）
INSERT INTO `admins` (`username`, `password`, `nickname`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 2, 1);

-- 插入彩种类型
INSERT INTO `lottery_types` (`type`, `name`, `color`, `icon`, `draw_time`, `draw_channel`, `description`, `sort`, `status`) VALUES
('ssq', '双色球', '#e60012', '🎱', '每周二、四、日 21:15开奖', '中国教育电视台一套（CETV-1）', '双色球是中国福利彩票的一种玩法，由中国福利彩票发行管理中心统一组织发行，在全国范围内销售。', 1, 1),
('qcl', '七乐彩', '#1890ff', '🎲', '每周一、三、五 21:15开奖', '中国教育电视台一套（CETV-1）', '七乐彩是中国福利彩票的一种玩法，从01-30共30个号码中选择7个号码组合为一注投注号码。', 2, 1),
('22x5', '22选5', '#52c41a', '🎯', '每周一至周日 20:30开奖', '中国教育电视台一套（CETV-1）', '22选5是中国福利彩票的一种玩法，从01-22共22个号码中选择5个号码组合为一注投注号码。', 3, 1),
('3d', '3D', '#faad14', '🎰', '每周一至周日 20:30开奖', '中国教育电视台一套（CETV-1）', '3D是中国福利彩票的一种玩法，投注者选择一个3位数进行投注，单选投注选中全部3个号码且顺序相同即为中奖。', 4, 1),
('kl8', '快乐8', '#722ed1', '🎡', '每周一至周日 21:30开奖', '中国教育电视台一套（CETV-1）', '快乐8是中国福利彩票的一种玩法，从01-80共80个号码中选择1-10个号码组合为一注投注号码。', 5, 1);

-- 插入规则
INSERT INTO `rules` (`type`, `name`, `draw_time`, `draw_channel`, `sort`, `status`) VALUES
('ssq', '双色球', '每周二、四、日 21:15（中国福利彩票发行管理中心公布为准）', '中国教育电视台一套（CETV-1）21:15现场直播', 1, 1),
('qcl', '七乐彩', '每周一、三、五 21:15（中国福利彩票发行管理中心公布为准）', '中国教育电视台一套（CETV-1）21:15现场直播', 2, 1),
('22x5', '22选5', '每周一至周日 20:30（中国福利彩票发行管理中心公布为准）', '中国教育电视台一套（CETV-1）20:30现场直播', 3, 1),
('3d', '3D', '每周一至周日 20:30（中国福利彩票发行管理中心公布为准）', '中国教育电视台一套（CETV-1）20:30现场直播', 4, 1),
('kl8', '快乐8', '每周一至周日 21:30（中国福利彩票发行管理中心公布为准）', '中国教育电视台一套（CETV-1）21:30现场直播', 5, 1);

-- 插入系统配置
INSERT INTO `settings` (`key`, `value`, `name`, `description`, `group`, `sort`, `status`) VALUES
('about_us', '{"content":"<p>福彩助手是一款专业的福彩查询应用，为您提供最新、最准确的福彩开奖信息查询服务。</p><p>我们致力于为彩民朋友提供便捷、及时、准确的开奖信息查询服务。</p><p><strong>联系我们：</strong></p><p>客服电话：400-xxx-xxxx</p><p>工作时间：9:00 - 18:00</p>"}', '关于我们', '关于我们页面内容', 'about', 1, 1),
('user_agreement', '{"content":"<p>欢迎您使用福彩助手！</p><p>为使用本应用服务，您应当阅读并遵守《用户协议》。</p><p>请您务必审慎阅读、充分理解各条款内容。</p>"}', '用户协议', '用户协议内容', 'agreement', 1, 1),
('privacy_policy', '{"content":"<p>保护用户隐私是本应用的一项基本政策。</p><p>本应用保证不对外公开或向第三方提供用户的注册资料。</p>"}', '隐私政策', '隐私政策内容', 'agreement', 2, 1),
('customer_service', '{"phone":"400-123-4567","work_time":"9:00-18:00","qq":"123456789","welcome_msg":"您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？"}', '客服配置', '在线客服相关配置', 'service', 1, 1);

-- 插入示例站点数据
INSERT INTO `stations` (`code`, `name`, `address`, `province`, `city`, `district`, `latitude`, `longitude`, `phone`, `business_hours`, `sort`, `status`) VALUES
('110101001', '北京市东城区福彩销售点001号', '北京市东城区王府井大街88号', '北京市', '北京市', '东城区', 39.9145, 116.404, '010-12345678', '9:00-21:00', 1, 1),
('110101002', '北京市东城区福彩销售点002号', '北京市东城区东四南大街50号', '北京市', '北京市', '东城区', 39.9189, 116.412, '010-12345679', '9:00-21:00', 2, 1),
('110102001', '北京市西城区福彩销售点001号', '北京市西城区西单北大街120号', '北京市', '北京市', '西城区', 39.9123, 116.379, '010-12345680', '9:00-21:00', 3, 1);

-- 插入示例开奖数据
INSERT INTO `lotteries` (`type`, `type_name`, `issue`, `red_balls`, `blue_balls`, `issue_date`, `status`) VALUES
('ssq', '双色球', '2024001', '[\"01\",\"05\",\"12\",\"18\",\"25\",\"30\"]', '[\"08\"]', '2024-01-02', 1),
('ssq', '双色球', '2024002', '[\"03\",\"08\",\"15\",\"22\",\"28\",\"33\"]', '[\"12\"]', '2024-01-04', 1),
('ssq', '双色球', '2024003', '[\"02\",\"09\",\"16\",\"21\",\"27\",\"31\"]', '[\"05\"]', '2024-01-07', 1),
('3d', '3D', '2024001', '[\"1\",\"2\",\"3\"]', '[]', '2024-01-01', 1),
('3d', '3D', '2024002', '[\"4\",\"5\",\"6\"]', '[]', '2024-01-02', 1),
('3d', '3D', '2024003', '[\"7\",\"8\",\"9\"]', '[]', '2024-01-03', 1),
('qcl', '七乐彩', '2024001', '[\"02\",\"05\",\"11\",\"17\",\"23\",\"26\",\"29\"]', '[\"08\"]', '2024-01-01', 1),
('22x5', '22选5', '2024001', '[\"03\",\"08\",\"14\",\"19\",\"21\"]', '[]', '2024-01-01', 1),
('kl8', '快乐8', '2024001', '[\"01\",\"05\",\"12\",\"18\",\"25\",\"33\",\"41\",\"48\",\"55\",\"62\",\"69\",\"77\"]', '[]', '2024-01-01', 1);

-- 插入示例新闻数据
INSERT INTO `news` (`type`, `title`, `summary`, `cover_image`, `source`, `is_banner`, `is_top`, `view_count`, `sort`, `status`, `publish_time`) VALUES
(2, '双色球第2024003期开奖：头奖2注1000万 奖池22.3亿', '双色球第2024003期开奖，全国共中出2注一等奖，单注奖金1000万元', NULL, '中国福彩网', 1, 1, 15680, 1, 1, '2024-01-07 21:30:00'),
(2, '3D第2024003期开奖：直选中出12345注', '3D第2024003期开奖号码：7 8 9，直选中出12345注，单注奖金1040元', NULL, '中国福彩网', 1, 0, 8920, 2, 1, '2024-01-07 20:30:00'),
(1, '福利彩票公益金使用情况公示', '根据《彩票管理条例》和《彩票公益金管理办法》，现将福利彩票公益金使用情况公示如下...', NULL, '中国福彩网', 0, 0, 5680, 3, 1, '2024-01-05 10:00:00'),
(1, '关于开展\"福彩进校园\"公益宣传活动的通知', '为进一步宣传福利彩票的公益性，现决定开展\"福彩进校园\"公益宣传活动...', NULL, '中国福彩网', 0, 0, 3250, 4, 1, '2024-01-03 09:00:00');
