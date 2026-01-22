<?php
/**
 * BaseController - المتحكم الأساسي
 * يحتوي على الدوال المشتركة لجميع المتحكمات
 */

namespace App\Controllers;

class BaseController {
    protected $viewPath;
    protected $layoutPath;
    protected $data = [];

    public function __construct() {
        // بدء الجلسة مرة واحدة فقط إذا لم تكن نشطة
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->viewPath = BASEPATH . '/views/';
        $this->layoutPath = BASEPATH . '/views/layouts/';
    }

    /**
     * تحميل View مع Layout
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        // تحميل المحتوى
        ob_start();
        $viewFile = $this->viewPath . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View not found: {$view}";
        }
        $content = ob_get_clean();

        // تحميل Layout
        $layoutFile = $this->layoutPath . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * تحميل View بدون Layout
     */
    protected function partial(string $view, array $data = []): void {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewFile = $this->viewPath . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
    }

    /**
     * إعادة توجيه
     */
    protected function redirect(string $url, int $statusCode = 302): void {
        // استخدام BASE_URL المعرف في config.php
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        
        // إذا كان المسار يبدأ بـ / نضيف BASE_URL
        if (str_starts_with($url, '/')) {
            $target = $baseUrl . $url;
        } else {
            $target = $url;
        }

        header("Location: {$target}", true, $statusCode);
        exit;
    }

    /**
     * إرجاع JSON
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * التحقق من تسجيل الدخول
     */
    protected function requireAuth(): array {
        // الجلسة تبدأ في constructor
        if (empty($_SESSION['user'])) {
            $this->redirect('/login.php');
        }
        return $_SESSION['user'];
    }

    /**
     * التحقق من صلاحيات الأدمن
     */
    protected function requireAdmin(): array {
        $user = $this->requireAuth();
        if (($user['role'] ?? '') !== 'admin') {
            $this->redirect('/dashboard.php?error=unauthorized');
        }
        return $user;
    }

    /**
     * الحصول على CSRF Token
     */
    protected function getCsrfToken(): string {
        // الجلسة تبدأ في constructor
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * التحقق من CSRF Token
     */
    protected function verifyCsrfToken(string $token): bool {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * الحصول على المستخدم الحالي
     */
    protected function currentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * تعيين رسالة Flash
     */
    protected function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * الحصول على رسالة Flash
     */
    protected function getFlash(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
