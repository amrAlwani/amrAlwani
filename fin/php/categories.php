<?php
/**
 * صفحة التصنيفات
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/CategoryController.php';

$controller = new \App\Controllers\CategoryController();
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($action) {
    case 'show':
        $controller->show($id);
        break;
    default:
        $controller->index();
}
