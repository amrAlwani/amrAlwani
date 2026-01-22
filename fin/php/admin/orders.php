<?php
/**
 * Admin Orders Entry Point
 */

// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASEPATH', dirname(__DIR__));

require_once BASEPATH . '/config/config.php';
require_once BASEPATH . '/config/database.php';
require_once BASEPATH . '/app/Controllers/Admin/OrderController.php';

$controller = new \App\Controllers\Admin\OrderController();
$action = $_GET['action'] ?? 'index';
$id = (int)($_GET['id'] ?? 0);

switch ($action) {
    case 'show':
        $controller->show($id);
        break;
    case 'update-status':
        $controller->updateStatus($id);
        break;
    default:
        $controller->index();
}
