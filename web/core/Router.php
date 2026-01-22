<?php
/**
 * Router - نظام التوجيه
 * يتعامل مع توجيه الطلبات للـ Controllers المناسبة
 */

class Router
{
    private array $routes = [];
    private array $apiRoutes = [];
    private string $prefix = '';
    private array $middleware = [];
    
    /**
     * إضافة مسار GET
     */
    public function get(string $path, string $handler, array $middleware = []): self
    {
        $this->addRoute('GET', $path, $handler, $middleware);
        return $this;
    }
    
    /**
     * إضافة مسار POST
     */
    public function post(string $path, string $handler, array $middleware = []): self
    {
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }
    
    /**
     * إضافة مسار PUT
     */
    public function put(string $path, string $handler, array $middleware = []): self
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
        return $this;
    }
    
    /**
     * إضافة مسار DELETE
     */
    public function delete(string $path, string $handler, array $middleware = []): self
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
        return $this;
    }
    
    /**
     * مجموعة مسارات مع prefix
     */
    public function group(string $prefix, callable $callback, array $middleware = []): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;
        
        $this->prefix = $previousPrefix . $prefix;
        $this->middleware = array_merge($previousMiddleware, $middleware);
        
        $callback($this);
        
        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
        
        return $this;
    }
    
    /**
     * إضافة مسار للقائمة
     */
    private function addRoute(string $method, string $path, string $handler, array $middleware = []): void
    {
        $fullPath = $this->prefix . $path;
        $allMiddleware = array_merge($this->middleware, $middleware);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $allMiddleware,
            'pattern' => $this->pathToPattern($fullPath)
        ];
    }
    
    /**
     * تحويل المسار لـ regex pattern
     */
    private function pathToPattern(string $path): string
    {
        // تحويل {param} إلى regex group
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * معالجة الطلب
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        // Handle OPTIONS for CORS
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // استخراج المعاملات
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // تنفيذ الـ middleware
                foreach ($route['middleware'] as $middleware) {
                    $this->runMiddleware($middleware);
                }
                
                // تنفيذ الـ handler
                $this->runHandler($route['handler'], $params);
                return;
            }
        }
        
        // لم يتم العثور على المسار
        $this->notFound();
    }
    
    /**
     * الحصول على URI نظيف
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // إزالة base path ديناميكياً
        $basePath = rtrim(BASE_URL, '/');
        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        return $uri ?: '/';
    }
    
    /**
     * تنفيذ الـ middleware
     */
    private function runMiddleware(string $middleware): void
    {
        switch ($middleware) {
            case 'auth':
                if (!isset($_SESSION['user'])) {
                    if ($this->isApiRequest()) {
                        Response::unauthorized('يرجى تسجيل الدخول');
                    }
                    header('Location: ' . url('login'));
                    exit;
                }
                break;
                
            case 'guest':
                if (isset($_SESSION['user'])) {
                    if ($this->isApiRequest()) {
                        Response::error('أنت مسجل الدخول بالفعل', [], 400);
                    }
                    header('Location: ' . url('dashboard'));
                    exit;
                }
                break;
                
            case 'admin':
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    if ($this->isApiRequest()) {
                        Response::forbidden('غير مصرح لك');
                    }
                    header('Location: ' . url('login'));
                    exit;
                }
                break;
                
            case 'api':
                header('Content-Type: application/json; charset=utf-8');
                break;
        }
    }
    
    /**
     * تنفيذ الـ handler
     */
    private function runHandler(string $handler, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $handler);
        
        $controllerFile = BASEPATH . '/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller not found: {$controllerName}");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            throw new Exception("Controller class not found: {$controllerName}");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method not found: {$controllerName}@{$method}");
        }
        
        call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * التحقق إذا كان طلب API
     */
    private function isApiRequest(): bool
    {
        return strpos($this->getUri(), '/api/') === 0;
    }
    
    /**
     * صفحة 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        
        if ($this->isApiRequest()) {
            Response::notFound('المسار غير موجود');
        }
        
        require_once BASEPATH . '/views/errors/404.php';
        exit;
    }
}
