<?php
/**
 * Controller - الفئة الأساسية للـ Controllers
 */

abstract class Controller
{
    protected ?array $user = null;
    
    public function __construct()
    {
        $this->user = $_SESSION['user'] ?? null;
    }
    
    /**
     * عرض View
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // استخراج البيانات كمتغيرات
        extract($data);
        
        // تحميل الـ View في buffer
        ob_start();
        $viewFile = BASEPATH . '/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        
        require $viewFile;
        $content = ob_get_clean();
        
        // تحميل الـ Layout
        if ($layout) {
            $layoutFile = BASEPATH . '/views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }
        
        echo $content;
    }
    
    /**
     * إعادة توجيه
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
    
    /**
     * الحصول على بيانات JSON من الطلب
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * الحصول على بيانات POST
     */
    protected function getPostData(): array
    {
        return $_POST;
    }
    
    /**
     * الحصول على بيانات GET
     */
    protected function getQueryParams(): array
    {
        return $_GET;
    }
    
    /**
     * التحقق من المستخدم
     */
    protected function requireAuth(): array
    {
        if (!$this->user) {
            if ($this->isApiRequest()) {
                Response::unauthorized('يرجى تسجيل الدخول');
            }
            $this->redirect('login');
        }
        return $this->user;
    }
    
    /**
     * التحقق من صلاحية المدير
     */
    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            if ($this->isApiRequest()) {
                Response::forbidden('غير مصرح لك');
            }
            $this->redirect('dashboard');
        }
        return $user;
    }
    
    /**
     * هل الطلب API؟
     */
    protected function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * إرجاع JSON
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * رسالة flash
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * الحصول على رسالة flash
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
