<?php
/**
 * صفحة سلة التسوق
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/CartController.php';

$controller = new \App\Controllers\CartController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'add':
        $controller->add();
        break;
    case 'update':
        $controller->update();
        break;
    case 'remove':
        $controller->remove();
        break;
    case 'clear':
        $controller->clear();
        break;
    default:
        $controller->index();
}
