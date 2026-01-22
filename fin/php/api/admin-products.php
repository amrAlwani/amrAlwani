<?php
/**
 * Admin Products API
 * واجهة برمجة إدارة المنتجات للأدمن
 * 
 * POST /api/admin-products.php?action=create - إنشاء منتج
 * PUT /api/admin-products.php?action=update&id=1 - تعديل منتج
 * DELETE /api/admin-products.php?action=delete&id=1 - حذف منتج
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/FileUpload.php';

// التحقق من JWT وصلاحيات الأدمن
$user = Auth::requireAuth();
if ($user['role'] !== 'admin') {
    // تسجيل محاولة وصول غير مصرح
    logSecurityEvent($user['id'], 'unauthorized_access', 'محاولة وصول لـ Admin Products API');
    Response::forbidden('هذه الصفحة للمسؤولين فقط');
}

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'create':
        if ($method !== 'POST') {
            Response::error('طريقة الطلب غير صحيحة', [], 405);
        }
        createProduct();
        break;
        
    case 'update':
        if ($method !== 'PUT' && $method !== 'POST') {
            Response::error('طريقة الطلب غير صحيحة', [], 405);
        }
        updateProduct();
        break;
        
    case 'delete':
        if ($method !== 'DELETE' && $method !== 'POST') {
            Response::error('طريقة الطلب غير صحيحة', [], 405);
        }
        deleteProduct();
        break;
        
    case 'list':
        listProducts();
        break;
        
    case 'get':
        getProduct();
        break;
        
    default:
        Response::error('إجراء غير صالح', [], 400);
}

/**
 * إنشاء منتج جديد
 * POST /api/admin-products.php?action=create
 */
function createProduct(): void {
    $db = db();
    
    // الحصول على البيانات
    $data = getRequestData();
    
    // التحقق من البيانات
    $validator = new Validator($data);
    $validator->required('name', 'اسم المنتج مطلوب')
              ->safeText('name', 'اسم المنتج يحتوي على رموز غير مسموحة')
              ->min('name', 2, 'اسم المنتج يجب أن يكون حرفين على الأقل')
              ->max('name', 200, 'اسم المنتج طويل جداً')
              ->required('price', 'السعر مطلوب')
              ->numeric('price', 'السعر يجب أن يكون رقماً')
              ->minValue('price', 0, 'السعر يجب أن يكون موجباً')
              ->required('category_id', 'التصنيف مطلوب')
              ->integer('category_id', 'التصنيف غير صالح');
    
    if (!empty($data['sale_price'])) {
        $validator->numeric('sale_price', 'سعر التخفيض يجب أن يكون رقماً')
                  ->minValue('sale_price', 0, 'سعر التخفيض يجب أن يكون موجباً');
    }
    
    if (!empty($data['description'])) {
        $validator->safeText('description', 'الوصف يحتوي على رموز غير مسموحة')
                  ->max('description', 5000, 'الوصف طويل جداً');
    }
    
    if (!empty($data['sku'])) {
        $validator->max('sku', 100, 'رمز SKU طويل جداً');
    }
    
    $validator->validate();
    
    // فحص XSS
    $data = sanitizeInput($data);
    
    try {
        // إنشاء slug
        $slug = createSlug($data['name']);
        
        // التحقق من عدم تكرار الـ slug
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
        
        // معالجة الصور
        $images = [];
        if (isset($_FILES['images'])) {
            $uploader = new FileUpload();
            $images = $uploader->uploadMultiple($_FILES['images'], 'products');
        } elseif (!empty($data['images'])) {
            $images = is_array($data['images']) ? $data['images'] : json_decode($data['images'], true);
        }
        
        $stmt = $db->prepare("
            INSERT INTO products (
                name, slug, description, price, sale_price, 
                category_id, stock_quantity, sku, weight,
                is_active, is_featured, images, created_at
            ) VALUES (
                :name, :slug, :description, :price, :sale_price,
                :category_id, :stock_quantity, :sku, :weight,
                :is_active, :is_featured, :images, NOW()
            )
        ");
        
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $slug,
            ':description' => $data['description'] ?? '',
            ':price' => (float)$data['price'],
            ':sale_price' => !empty($data['sale_price']) ? (float)$data['sale_price'] : null,
            ':category_id' => (int)$data['category_id'],
            ':stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            ':sku' => $data['sku'] ?? null,
            ':weight' => !empty($data['weight']) ? (float)$data['weight'] : null,
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ':is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            ':images' => json_encode($images),
        ]);
        
        $productId = $db->lastInsertId();
        
        // تسجيل الحدث
        logSecurityEvent(Auth::getUser()['id'], 'product_created', "تم إنشاء المنتج: {$data['name']}");
        
        // جلب المنتج المُنشأ
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $product['images'] = json_decode($product['images'], true);
        
        Response::created($product, 'تم إنشاء المنتج بنجاح');
        
    } catch (PDOException $e) {
        Response::serverError('فشل إنشاء المنتج');
    }
}

/**
 * تعديل منتج
 * PUT /api/admin-products.php?action=update&id=1
 */
function updateProduct(): void {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        Response::error('معرف المنتج مطلوب', [], 400);
    }
    
    $db = db();
    $data = getRequestData();
    
    // فحص XSS
    $data = sanitizeInput($data);
    
    try {
        // التحقق من وجود المنتج
        $checkStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $checkStmt->execute([$id]);
        $existingProduct = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingProduct) {
            Response::notFound('المنتج غير موجود');
        }
        
        // بناء استعلام التحديث
        $updates = [];
        $params = [':id' => $id];
        
        $allowedFields = [
            'name', 'description', 'price', 'sale_price', 
            'category_id', 'stock_quantity', 'sku', 'weight',
            'is_active', 'is_featured'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        // تحديث الـ slug إذا تغير الاسم
        if (isset($data['name']) && $data['name'] !== $existingProduct['name']) {
            $slug = createSlug($data['name']);
            $checkSlug = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
            $checkSlug->execute([$slug, $id]);
            if ($checkSlug->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            $updates[] = "slug = :slug";
            $params[':slug'] = $slug;
        }
        
        // معالجة الصور
        if (isset($_FILES['images'])) {
            $uploader = new FileUpload();
            $newImages = $uploader->uploadMultiple($_FILES['images'], 'products');
            $updates[] = "images = :images";
            $params[':images'] = json_encode($newImages);
        } elseif (isset($data['images'])) {
            $images = is_array($data['images']) ? $data['images'] : json_decode($data['images'], true);
            $updates[] = "images = :images";
            $params[':images'] = json_encode($images);
        }
        
        if (empty($updates)) {
            Response::error('لا توجد بيانات للتحديث', [], 400);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // تسجيل الحدث
        logSecurityEvent(Auth::getUser()['id'], 'product_updated', "تم تعديل المنتج ID: {$id}");
        
        // جلب المنتج المُحدث
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $product['images'] = json_decode($product['images'], true);
        
        Response::success($product, 'تم تعديل المنتج بنجاح');
        
    } catch (PDOException $e) {
        Response::serverError('فشل تعديل المنتج');
    }
}

/**
 * حذف منتج
 * DELETE /api/admin-products.php?action=delete&id=1
 */
function deleteProduct(): void {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        Response::error('معرف المنتج مطلوب', [], 400);
    }
    
    $db = db();
    
    try {
        // التحقق من وجود المنتج
        $checkStmt = $db->prepare("SELECT name FROM products WHERE id = ?");
        $checkStmt->execute([$id]);
        $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            Response::notFound('المنتج غير موجود');
        }
        
        // حذف المنتج (soft delete أو hard delete)
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        // تسجيل الحدث
        logSecurityEvent(Auth::getUser()['id'], 'product_deleted', "تم حذف المنتج: {$product['name']}");
        
        Response::success(null, 'تم حذف المنتج بنجاح');
        
    } catch (PDOException $e) {
        // قد يكون هناك منتجات مرتبطة بطلبات
        if ($e->getCode() == '23000') {
            Response::error('لا يمكن حذف المنتج لوجود طلبات مرتبطة به', [], 400);
        }
        Response::serverError('فشل حذف المنتج');
    }
}

/**
 * قائمة المنتجات للأدمن
 */
function listProducts(): void {
    $db = db();
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    
    try {
        $countStmt = $db->query("SELECT COUNT(*) FROM products");
        $total = (int)$countStmt->fetchColumn();
        
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as &$product) {
            $product['images'] = json_decode($product['images'], true) ?? [];
        }
        
        Response::paginate($products, $total, $page, $perPage);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب المنتجات');
    }
}

/**
 * تفاصيل منتج
 */
function getProduct(): void {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        Response::error('معرف المنتج مطلوب', [], 400);
    }
    
    $db = db();
    
    try {
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            Response::notFound('المنتج غير موجود');
        }
        
        $product['images'] = json_decode($product['images'], true) ?? [];
        
        Response::success($product);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب المنتج');
    }
}

/**
 * الحصول على بيانات الطلب
 */
function getRequestData(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    return $_POST;
}

/**
 * تنظيف المدخلات من XSS
 */
function sanitizeInput(array $data): array {
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            // إزالة علامات HTML الخطيرة
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // فحص SQL Injection patterns
            if (preg_match('/(\bSELECT\b|\bUNION\b|\bINSERT\b|\bDROP\b|\bDELETE\b)/i', $value)) {
                logSecurityEvent(Auth::getUser()['id'] ?? null, 'sql_injection', "محاولة SQL Injection: {$value}");
                Response::forbidden('تم اكتشاف نشاط مشبوه');
            }
            
            // فحص XSS patterns
            if (preg_match('/<script|javascript:|on\w+=/i', $value)) {
                logSecurityEvent(Auth::getUser()['id'] ?? null, 'xss_attempt', "محاولة XSS: {$value}");
                Response::forbidden('تم اكتشاف نشاط مشبوه');
            }
        }
    }
    
    return $data;
}

/**
 * إنشاء slug من النص
 */
function createSlug(string $text): string {
    // تحويل للحروف الصغيرة
    $slug = mb_strtolower($text, 'UTF-8');
    // استبدال المسافات بشرطات
    $slug = preg_replace('/\s+/', '-', $slug);
    // إزالة الأحرف الخاصة
    $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug);
    // إزالة الشرطات المتكررة
    $slug = preg_replace('/-+/', '-', $slug);
    // إزالة الشرطات من البداية والنهاية
    return trim($slug, '-');
}

/**
 * تسجيل حدث أمني
 */
function logSecurityEvent(?int $userId, string $action, string $description): void {
    try {
        $db = db();
        $stmt = $db->prepare("
            INSERT INTO security_logs (user_id, action_type, description, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, :description, :ip, :ua, NOW())
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':description' => $description,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    } catch (PDOException $e) {
        // تجاهل أخطاء التسجيل
    }
}
