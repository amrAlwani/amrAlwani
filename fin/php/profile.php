<?php
/**
 * صفحة الملف الشخصي
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/ProfileController.php';

$controller = new \App\Controllers\ProfileController();
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($action) {
    case 'edit':
        $controller->edit();
        break;
    case 'update':
        $controller->update();
        break;
    case 'change-password':
        $controller->changePassword();
        break;
    case 'addresses':
        $controller->addresses();
        break;
    case 'add-address':
        $controller->addAddress();
        break;
    case 'delete-address':
        $controller->deleteAddress($id);
        break;
    default:
        $controller->index();
}
