<?php
/**
 * صفحة تفاصيل المنتج
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/ProductController.php';

$controller = new \App\Controllers\ProductController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $controller->show($id);
} else {
    header('Location: /products.php');
    exit;
}
