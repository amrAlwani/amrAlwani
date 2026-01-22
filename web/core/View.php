<?php
/**
 * View - فئة مساعدة للـ Views
 */

class View
{
    /**
     * عرض View
     */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        
        ob_start();
        $viewFile = BASEPATH . '/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        
        require $viewFile;
        $content = ob_get_clean();
        
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
     * تضمين جزء من View
     */
    public static function partial(string $partial, array $data = []): void
    {
        extract($data);
        
        $partialFile = BASEPATH . '/views/partials/' . str_replace('.', '/', $partial) . '.php';
        
        if (file_exists($partialFile)) {
            require $partialFile;
        }
    }
    
    /**
     * تنسيق العملة
     */
    public static function currency(float $amount): string
    {
        return number_format($amount, 2) . ' ' . CURRENCY_SYMBOL;
    }
    
    /**
     * تنسيق التاريخ
     */
    public static function date(string $date, string $format = 'd/m/Y'): string
    {
        return date($format, strtotime($date));
    }
    
    /**
     * تنسيق التاريخ والوقت
     */
    public static function datetime(string $date, string $format = 'd/m/Y H:i'): string
    {
        return date($format, strtotime($date));
    }
    
    /**
     * اختصار النص
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    /**
     * تحويل النص لـ HTML آمن
     */
    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * التحقق من وجود رسالة flash
     */
    public static function hasFlash(): bool
    {
        return isset($_SESSION['flash']);
    }
    
    /**
     * عرض رسالة flash
     */
    public static function flash(): void
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $type = $flash['type'];
            $message = $flash['message'];
            
            $colors = [
                'success' => 'bg-green-100 border-green-500 text-green-700',
                'error' => 'bg-red-100 border-red-500 text-red-700',
                'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
                'info' => 'bg-blue-100 border-blue-500 text-blue-700',
            ];
            
            $color = $colors[$type] ?? $colors['info'];
            
            echo "<div class=\"border-r-4 p-4 mb-4 {$color}\" role=\"alert\">";
            echo "<p>" . self::escape($message) . "</p>";
            echo "</div>";
            
            unset($_SESSION['flash']);
        }
    }
    
    /**
     * رابط نشط
     */
    public static function activeLink(string $path): string
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $checkPath = url($path);
        
        return $currentPath === $checkPath ? 'bg-blue-700' : '';
    }
}
