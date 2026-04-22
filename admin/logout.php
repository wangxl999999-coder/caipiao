<?php
/**
 * 退出登录
 */

require_once __DIR__ . '/config/config.php';

session_unset();
session_destroy();

redirect('login.php');
