<?php
/**
 * 管理后台配置文件
 */

define('ADMIN_ROOT', dirname(__DIR__));
define('ADMIN_VIEWS', ADMIN_ROOT . '/views');
define('ADMIN_ASSETS', ADMIN_ROOT . '/assets');

session_start();

require_once ADMIN_ROOT . '/../api/core/Database.php';
require_once ADMIN_ROOT . '/../api/core/Model.php';
require_once ADMIN_ROOT . '/../api/config/database.php';

use Core\Database;

function getDatabase()
{
    static $db = null;
    if ($db === null) {
        $db = Database::getInstance();
    }
    return $db;
}

function isLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

function getCurrentAdmin()
{
    if (isset($_SESSION['admin'])) {
        return $_SESSION['admin'];
    }
    return null;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function jsonResponse($success, $message = '', $data = null)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function getInput($key, $default = null)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $default;
}

function getPage()
{
    return max(1, (int)getInput('page', 1));
}

function getPageSize()
{
    return max(1, min(100, (int)getInput('pageSize', 15)));
}

function formatDate($date)
{
    if (!$date) {
        return '';
    }
    return date('Y-m-d H:i:s', strtotime($date));
}

function formatDateShort($date)
{
    if (!$date) {
        return '';
    }
    return date('Y-m-d', strtotime($date));
}

function escape($str)
{
    if ($str === null) {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
