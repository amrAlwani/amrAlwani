<?php

namespace App\Core;

/**
 * فئة Controller الأساسية
 * توفر وظائف مشتركة لجميع controllers في التطبيق
 */
class Controller
{
    /**
     * تحميل وعرض ملف view
     * @param string $view اسم ملف الـ view
     * @param array $data البيانات المراد تمريرها
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $baseViewPath = Config::get('app.paths.views');
        $viewPath = $baseViewPath . '/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
            return;
        }

        if (Config::get('app.debug')) {
            echo "<h3>View Not Found</h3>";
            echo "<p>{$view}.php</p>";
            echo "<p>Path: {$viewPath}</p>";
        }

        die('View not found');
    }

    /**
     * إرجاع استجابة JSON
     * @param mixed $data البيانات
     * @param int $statusCode كود الحالة HTTP
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * إعادة توجيه المستخدم
     * @param string $path المسار
     */
    protected function redirect(string $path): void
    {
        if (preg_match('#^https?://#', $path)) {
            $url = $path;
        } else {
            $url = Path::url($path);
        }

        header("Location: $url");
        exit;
    }

    /**
     * تحميل model
     * @param string $model اسم الـ model
     * @return object
     */
    protected function model($model)
    {
        $modelClass = "App\\Models\\{$model}";

        if (class_exists($modelClass)) {
            return new $modelClass();
        }

        die("النموذج '{$model}' غير موجود");
    }

    /**
     * الحصول على بيانات الطلب (JSON أو POST)
     * @return array
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }
        
        return $_POST;
    }

    /**
     * التحقق من أن الطلب AJAX أو API
     */
    protected function isApiRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            || strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }

    /**
     * التحقق من المصادقة
     */
    protected function requireAuth(): ?array
    {
        $auth = new \App\Core\Auth();
        $token = $this->getBearerToken();
        
        if (!$token) {
            $this->json(['success' => false, 'message' => 'غير مصرح'], 401);
            return null;
        }
        
        $user = $auth->validateToken($token);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'جلسة منتهية'], 401);
            return null;
        }
        
        return $user;
    }

    /**
     * استخراج Bearer Token من الـ Header
     */
    protected function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
