<?php
/**
 * Security Dashboard Entry Point
 * نقطة دخول لوحة مراقبة الأمان
 */

session_start();

// التحقق من تسجيل الدخول والصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../app/Controllers/Admin/SecurityController.php';

$controller = new \App\Controllers\Admin\SecurityController();
$controller->dashboard();
