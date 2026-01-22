<?php
/**
 * Admin Products Entry Point
 */

// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASEPATH', dirname(__DIR__));

require_once BASEPATH . '/config/config.php';
require_once BASEPATH . '/config/database.php';
require_once BASEPATH . '/app/Controllers/Admin/ProductController.php';

$controller = new \App\Controllers\Admin\ProductController();
$action = $_GET['action'] ?? 'index';
$id = (int)($_GET['id'] ?? 0);

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'edit':
        $controller->edit($id);
        break;
    case 'update':
        $controller->update($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    default:
        $controller->index();
}
