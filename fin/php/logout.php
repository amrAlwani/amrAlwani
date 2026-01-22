<?php
/**
 * تسجيل الخروج
 */
session_start();

// تسجيل في سجل الأمان
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config/database.php';
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $logStmt = $db->prepare("INSERT INTO security_logs (user_id, action, ip_address, user_agent) VALUES (:user_id, 'logout', :ip, :ua)");
        $logStmt->execute([
            'user_id' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'ua' => $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (PDOException $e) {
        // تجاهل الخطأ
    }
}

// تدمير الجلسة
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header('Location: login.php');
exit;
