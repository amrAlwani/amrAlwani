<?php
/**
 * Orders API
 * واجهة برمجة الطلبات
 * 
 * تم التحسين: إضافة Validation شامل
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../models/Order.php';

$action = $_GET['action'] ?? 'list';

// جميع عمليات الطلبات تتطلب مصادقة
$user = Auth::requireAuth();
$orderModel = new Order();

switch ($action) {
    case 'list':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 10)));
        
        $result = $orderModel->getByUser($user['id'], $page, $perPage);
        Response::paginate($result['orders'], $result['total'], $page, $perPage);
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        
        if (!$id || !is_numeric($id)) {
            Response::error('معرف الطلب غير صالح', [], 400);
        }
        
        $order = $orderModel->findById((int)$id);
        
        if (!$order || $order['user_id'] != $user['id']) {
            Response::notFound('الطلب غير موجود');
        }
        
        Response::success($order);
        break;

    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error('بيانات غير صالحة', [], 400);
        }
        
        // تنظيف البيانات
        $data = Validator::sanitizeArray($data);
        
        // التحقق من عنوان الشحن
        $validator = new Validator($data);
        
        if (!empty($data['shipping_name'])) {
            $validator->safeName('shipping_name', 'اسم المستلم يحتوي على رموز غير مسموحة')
                      ->max('shipping_name', 100, 'اسم المستلم طويل جداً');
        }
        
        if (!empty($data['shipping_phone'])) {
            $validator->phone('shipping_phone', 'رقم هاتف المستلم غير صالح');
        }
        
        if (!empty($data['shipping_city'])) {
            $validator->safeText('shipping_city', 'اسم المدينة يحتوي على رموز غير مسموحة')
                      ->max('shipping_city', 100, 'اسم المدينة طويل جداً');
        }
        
        if (!empty($data['shipping_address'])) {
            $validator->safeText('shipping_address', 'العنوان يحتوي على رموز غير مسموحة')
                      ->max('shipping_address', 500, 'العنوان طويل جداً');
        }
        
        if (!empty($data['notes'])) {
            $validator->safeText('notes', 'الملاحظات تحتوي على رموز غير مسموحة')
                      ->max('notes', 1000, 'الملاحظات طويلة جداً');
        }
        
        if (!empty($data['payment_method'])) {
            $validator->in('payment_method', ['cod', 'card', 'bank', 'wallet'], 'طريقة الدفع غير صالحة');
        }
        
        $validator->validate();
        
        $result = $orderModel->create($user['id'], $data);
        
        if (!$result['success']) {
            Response::error($result['message'] ?? 'فشل إنشاء الطلب', $result['errors'] ?? [], 400);
        }
        
        Response::created($result['order'], 'تم إنشاء الطلب بنجاح');
        break;

    case 'cancel':
        $id = $_GET['id'] ?? null;
        
        if (!$id || !is_numeric($id)) {
            Response::error('معرف الطلب غير صالح', [], 400);
        }
        
        $result = $orderModel->cancel((int)$id, $user['id']);
        
        if (!$result['success']) {
            Response::error($result['message'], [], 400);
        }
        
        Response::success($result['order'], 'تم إلغاء الطلب');
        break;

    case 'track':
        $orderNumber = $_GET['order_number'] ?? null;
        
        if (!$orderNumber) {
            Response::error('رقم الطلب مطلوب', [], 400);
        }
        
        // تنظيف رقم الطلب
        $orderNumber = Validator::sanitize($orderNumber);
        
        // التحقق من صيغة رقم الطلب
        if (!preg_match('/^[A-Z0-9\-]+$/i', $orderNumber) || mb_strlen($orderNumber) > 50) {
            Response::error('رقم الطلب غير صالح', [], 400);
        }
        
        $order = $orderModel->findByOrderNumber($orderNumber);
        
        if (!$order || $order['user_id'] != $user['id']) {
            Response::notFound('الطلب غير موجود');
        }
        
        Response::success([
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'payment_status' => $order['payment_status'],
            'created_at' => $order['created_at'],
            'shipped_at' => $order['shipped_at'],
            'delivered_at' => $order['delivered_at']
        ]);
        break;

    default:
        Response::error('إجراء غير صالح', [], 400);
}
