<?php
/**
 * Security Monitoring API
 * واجهة برمجة مراقبة الأمان
 * 
 * يوفر:
 * - سجل الأحداث الأمنية
 * - ملخص إحصائي
 * - تحليل الهجمات
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';

$action = $_GET['action'] ?? 'events';

// التحقق من صلاحيات الأدمن
$user = Auth::requireAuth();
if ($user['role'] !== 'admin') {
    Response::forbidden('هذه الصفحة للمسؤولين فقط');
}

switch ($action) {
    case 'events':
        getSecurityEvents();
        break;
    case 'summary':
        getSecuritySummary();
        break;
    case 'login-attempts':
        getLoginAttempts();
        break;
    case 'threats':
        getThreats();
        break;
    case 'user-activity':
        getUserActivity();
        break;
    default:
        Response::error('إجراء غير صالح', [], 400);
}

/**
 * الحصول على سجل الأحداث الأمنية
 * GET /api/security.php?action=events
 */
function getSecurityEvents(): void {
    $db = db();
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    
    // فلاتر
    $level = $_GET['level'] ?? null; // high, medium, low
    $type = $_GET['type'] ?? null; // login, logout, api_access, attack
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    
    $where = ["1=1"];
    $params = [];
    
    if ($level) {
        $where[] = "sl.severity_level = :level";
        $params[':level'] = $level;
    }
    
    if ($type) {
        $where[] = "sl.action_type = :type";
        $params[':type'] = $type;
    }
    
    if ($dateFrom) {
        $where[] = "sl.created_at >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $where[] = "sl.created_at <= :date_to";
        $params[':date_to'] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = implode(' AND ', $where);
    
    try {
        // العد الإجمالي
        $countSql = "SELECT COUNT(*) FROM security_logs sl WHERE {$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        // جلب الأحداث
        $sql = "SELECT sl.*, u.name as user_name, u.email as user_email
                FROM security_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE {$whereClause}
                ORDER BY sl.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // تصنيف الخطورة
        foreach ($events as &$event) {
            $event['severity'] = classifySeverity($event['action_type']);
            $event['severity_label'] = getSeverityLabel($event['severity']);
        }
        
        Response::paginate($events, $total, $page, $perPage);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب الأحداث الأمنية');
    }
}

/**
 * الحصول على الملخص الأمني
 * GET /api/security.php?action=summary
 */
function getSecuritySummary(): void {
    $db = db();
    
    try {
        // إجمالي محاولات الدخول اليوم
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM login_attempts 
            WHERE DATE(attempted_at) = CURDATE()
        ");
        $loginAttemptsToday = (int)$stmt->fetch()['total'];
        
        // المحاولات الفاشلة اليوم
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM login_attempts 
            WHERE DATE(attempted_at) = CURDATE() 
            AND attempt_status = 'failed'
        ");
        $failedAttemptsToday = (int)$stmt->fetch()['total'];
        
        // المحاولات الناجحة اليوم
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM login_attempts 
            WHERE DATE(attempted_at) = CURDATE() 
            AND attempt_status = 'success'
        ");
        $successfulAttemptsToday = (int)$stmt->fetch()['total'];
        
        // الهجمات المكتشفة (Brute Force, XSS, SQL Injection)
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM security_logs 
            WHERE DATE(created_at) = CURDATE()
            AND action_type IN ('brute_force', 'xss_attempt', 'sql_injection', 'suspicious_activity')
        ");
        $attacksDetected = (int)$stmt->fetch()['total'];
        
        // الحسابات المقفلة
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM users 
            WHERE account_locked_until > NOW()
        ");
        $lockedAccounts = (int)$stmt->fetch()['total'];
        
        // المستخدمين النشطين (آخر 24 ساعة)
        $stmt = $db->query("
            SELECT COUNT(DISTINCT user_id) as total 
            FROM user_sessions 
            WHERE last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND is_active = 1
        ");
        $activeUsers = (int)$stmt->fetch()['total'];
        
        // أكثر IPs مشبوهة
        $stmt = $db->query("
            SELECT ip_address, COUNT(*) as attempts
            FROM login_attempts
            WHERE attempt_status = 'failed'
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY ip_address
            HAVING attempts >= 3
            ORDER BY attempts DESC
            LIMIT 5
        ");
        $suspiciousIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // إحصائيات الأسبوع
        $stmt = $db->query("
            SELECT 
                DATE(attempted_at) as date,
                SUM(CASE WHEN attempt_status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN attempt_status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM login_attempts
            WHERE attempted_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(attempted_at)
            ORDER BY date ASC
        ");
        $weeklyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Response::success([
            'today' => [
                'total_login_attempts' => $loginAttemptsToday,
                'successful_attempts' => $successfulAttemptsToday,
                'failed_attempts' => $failedAttemptsToday,
                'attacks_detected' => $attacksDetected,
                'locked_accounts' => $lockedAccounts,
                'active_users' => $activeUsers,
            ],
            'suspicious_ips' => $suspiciousIPs,
            'weekly_stats' => $weeklyStats,
            'security_score' => calculateSecurityScore($failedAttemptsToday, $attacksDetected, $lockedAccounts),
        ]);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب الملخص الأمني');
    }
}

/**
 * الحصول على محاولات تسجيل الدخول
 * GET /api/security.php?action=login-attempts
 */
function getLoginAttempts(): void {
    $db = db();
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    
    $status = $_GET['status'] ?? null; // success, failed
    
    try {
        $where = ["1=1"];
        $params = [];
        
        if ($status) {
            $where[] = "la.attempt_status = :status";
            $params[':status'] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // العد
        $countSql = "SELECT COUNT(*) FROM login_attempts la WHERE {$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        // الجلب
        $sql = "SELECT la.*, u.name as user_name
                FROM login_attempts la
                LEFT JOIN users u ON la.email = u.email
                WHERE {$whereClause}
                ORDER BY la.attempted_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Response::paginate($attempts, $total, $page, $perPage);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب محاولات الدخول');
    }
}

/**
 * الحصول على الهجمات المكتشفة
 * GET /api/security.php?action=threats
 */
function getThreats(): void {
    $db = db();
    
    try {
        $sql = "SELECT sl.*, u.name as user_name, u.email as user_email
                FROM security_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.action_type IN ('brute_force', 'xss_attempt', 'sql_injection', 'suspicious_activity', 'unauthorized_access')
                ORDER BY sl.created_at DESC
                LIMIT 50";
        
        $stmt = $db->query($sql);
        $threats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($threats as &$threat) {
            $threat['severity'] = 'high';
            $threat['severity_label'] = 'خطورة عالية';
            $threat['threat_type_label'] = getThreatLabel($threat['action_type']);
        }
        
        Response::success($threats);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب التهديدات');
    }
}

/**
 * الحصول على نشاط مستخدم معين
 * GET /api/security.php?action=user-activity&user_id=1
 */
function getUserActivity(): void {
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        Response::error('معرف المستخدم مطلوب', [], 400);
    }
    
    $db = db();
    
    try {
        // أحداث المستخدم
        $stmt = $db->prepare("
            SELECT * FROM security_logs 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([':user_id' => $userId]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // جلسات المستخدم
        $stmt = $db->prepare("
            SELECT * FROM user_sessions 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([':user_id' => $userId]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // محاولات الدخول
        $stmt = $db->prepare("
            SELECT la.* FROM login_attempts la
            JOIN users u ON la.email = u.email
            WHERE u.id = :user_id 
            ORDER BY la.attempted_at DESC 
            LIMIT 20
        ");
        $stmt->execute([':user_id' => $userId]);
        $loginAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Response::success([
            'events' => $events,
            'sessions' => $sessions,
            'login_attempts' => $loginAttempts,
        ]);
        
    } catch (PDOException $e) {
        Response::serverError('فشل جلب نشاط المستخدم');
    }
}

/**
 * تصنيف مستوى الخطورة
 */
function classifySeverity(string $actionType): string {
    $highSeverity = ['brute_force', 'xss_attempt', 'sql_injection', 'unauthorized_access', 'suspicious_activity'];
    $mediumSeverity = ['login_failed', 'password_reset_failed', 'invalid_token'];
    
    if (in_array($actionType, $highSeverity)) {
        return 'high';
    } elseif (in_array($actionType, $mediumSeverity)) {
        return 'medium';
    }
    return 'low';
}

/**
 * الحصول على تسمية مستوى الخطورة
 */
function getSeverityLabel(string $severity): string {
    return match($severity) {
        'high' => 'خطورة عالية',
        'medium' => 'خطورة متوسطة',
        'low' => 'خطورة منخفضة',
        default => 'غير محدد',
    };
}

/**
 * الحصول على تسمية نوع التهديد
 */
function getThreatLabel(string $type): string {
    return match($type) {
        'brute_force' => 'هجوم Brute Force',
        'xss_attempt' => 'محاولة XSS',
        'sql_injection' => 'محاولة SQL Injection',
        'unauthorized_access' => 'وصول غير مصرح',
        'suspicious_activity' => 'نشاط مشبوه',
        default => $type,
    };
}

/**
 * حساب درجة الأمان
 */
function calculateSecurityScore(int $failedAttempts, int $attacks, int $lockedAccounts): array {
    $score = 100;
    
    // خصم نقاط للمحاولات الفاشلة
    $score -= min(20, $failedAttempts * 2);
    
    // خصم نقاط للهجمات
    $score -= min(40, $attacks * 10);
    
    // خصم نقاط للحسابات المقفلة
    $score -= min(20, $lockedAccounts * 5);
    
    $score = max(0, $score);
    
    $status = match(true) {
        $score >= 80 => 'excellent',
        $score >= 60 => 'good',
        $score >= 40 => 'moderate',
        $score >= 20 => 'poor',
        default => 'critical',
    };
    
    $statusLabel = match($status) {
        'excellent' => 'ممتاز',
        'good' => 'جيد',
        'moderate' => 'متوسط',
        'poor' => 'ضعيف',
        'critical' => 'حرج',
    };
    
    return [
        'score' => $score,
        'status' => $status,
        'status_label' => $statusLabel,
    ];
}
