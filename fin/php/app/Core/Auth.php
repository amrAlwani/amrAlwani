<?php

namespace App\Core;

require_once dirname(__DIR__, 2) . '/config/database.php';

/**
 * فئة Auth - نظام المصادقة الآمن المتكامل
 * يتضمن: Rate Limiting, MFA, Security Logging
 */
class Auth
{
    private \PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = db();
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
    }

    /**
     * تسجيل مستخدم جديد
     */
    public function register(array $data): array
    {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'البريد الإلكتروني وكلمة المرور مطلوبان'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'البريد الإلكتروني غير صالح'];
        }

        $passwordStrength = $this->checkPasswordStrength($data['password'], $data['role'] ?? 'user');
        if (!$passwordStrength['valid']) {
            return ['success' => false, 'message' => $passwordStrength['message']];
        }

        if ($this->userExists($data['email'])) {
            return ['success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقًا'];
        }

        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, [
                'cost' => $this->config['bcrypt_cost'] ?? 12
            ]);

            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, phone, password, role)
                VALUES (:name, :email, :phone, :password, :role)
            ");

            $stmt->execute([
                ':name' => $data['name'] ?? '',
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? '0000000000',
                ':password' => $hashedPassword,
                ':role' => $data['role'] ?? 'user'
            ]);

            $userId = $this->db->lastInsertId();

            $this->logSecurityEvent($userId, 'REGISTRATION', 'تم تسجيل مستخدم جديد');

            return [
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'user_id' => $userId
            ];

        } catch (\PDOException $e) {
            error_log("فشل التسجيل: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'حدث خطأ تقني. الرجاء المحاولة لاحقًا.'
            ];
        }
    }

    /**
     * تسجيل الدخول الآمن
     */
    public function login(string $email, string $password, ?string $mfaCode = null): array
    {
        if ($this->isIpRateLimited()) {
            return [
                'success' => false,
                'message' => 'تم تجاوز عدد المحاولات المسموح بها. الرجاء الانتظار 15 دقيقة.'
            ];
        }

        $attemptId = $this->logLoginAttempt($email);

        $user = $this->getUserByEmail($email);

        if (!$user) {
            $this->updateLoginAttempt($attemptId, 'wrong_password');
            $this->delayResponse();
            return $this->genericErrorResponse();
        }

        if (!($user['is_active'] ?? true)) {
            $this->updateLoginAttempt($attemptId, 'account_locked');
            return [
                'success' => false,
                'message' => 'الحساب غير مفعل'
            ];
        }

        if (!empty($user['account_locked_until']) && strtotime($user['account_locked_until']) > time()) {
            $this->updateLoginAttempt($attemptId, 'account_locked');
            return [
                'success' => false,
                'message' => 'الحساب مقفل مؤقتًا بسبب محاولات دخول فاشلة متعددة'
            ];
        }

        if (!password_verify($password, $user['password'])) {
            $this->incrementFailedAttempts($user['id']);
            $this->updateLoginAttempt($attemptId, 'wrong_password');
            $this->delayResponse();
            return $this->genericErrorResponse();
        }

        if (!empty($user['mfa_enabled']) && !empty($user['mfa_secret'])) {
            if (!$mfaCode) {
                $this->updateLoginAttempt($attemptId, 'mfa_failed');
                return [
                    'success' => false,
                    'message' => 'مطلوب رمز التحقق الثنائي',
                    'requires_mfa' => true
                ];
            }

            if (!$this->verifyMFA($user['mfa_secret'], $mfaCode)) {
                if (!$this->verifyBackupCode($user['id'], $mfaCode)) {
                    $this->updateLoginAttempt($attemptId, 'mfa_failed');
                    return [
                        'success' => false,
                        'message' => 'رمز التحقق الثنائي غير صحيح'
                    ];
                }
            }
        }

        $this->resetFailedAttempts($user['id']);
        $this->updateLastLogin($user['id']);
        $this->updateLoginAttempt($attemptId, 'success');

        $token = $this->generateToken($user);

        $this->logSecurityEvent($user['id'], 'LOGIN_SUCCESS', 'تم تسجيل الدخول بنجاح');

        return [
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar'] ?? null
            ]
        ];
    }

    /**
     * التحقق من صحة التوكن
     */
    public function validateToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT us.*, u.*
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            WHERE us.session_token = :token
            AND us.expires_at > NOW()
            AND us.is_revoked = FALSE
            AND (u.is_active = TRUE OR u.is_active IS NULL)
        ");

        $stmt->execute([':token' => $token]);
        $session = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        $currentFingerprint = $this->generateDeviceFingerprint();
        if (!empty($session['device_fingerprint']) && $session['device_fingerprint'] !== $currentFingerprint) {
            $this->revokeToken($token);
            return null;
        }

        return [
            'id' => $session['user_id'],
            'name' => $session['name'],
            'email' => $session['email'],
            'role' => $session['role']
        ];
    }

    /**
     * تسجيل الخروج
     */
    public function logout(string $token): bool
    {
        $session = $this->validateToken($token);

        if ($session) {
            $this->revokeToken($token);
            $this->logSecurityEvent($session['id'], 'LOGOUT', 'تم تسجيل الخروج');
        }

        return true;
    }

    /**
     * إنشاء توكن آمن
     */
    private function generateToken(array $user): string
    {
        $token = bin2hex(random_bytes(32));
        $deviceFingerprint = $this->generateDeviceFingerprint();

        $stmt = $this->db->prepare("
            INSERT INTO user_sessions
            (user_id, session_token, ip_address, user_agent, device_fingerprint, expires_at)
            VALUES (:user_id, :token, :ip, :ua, :device, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");

        $stmt->execute([
            ':user_id' => $user['id'],
            ':token' => $token,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ':device' => $deviceFingerprint
        ]);

        return $token;
    }

    /**
     * إلغاء التوكن
     */
    private function revokeToken(string $token): void
    {
        $stmt = $this->db->prepare("
            UPDATE user_sessions
            SET is_revoked = TRUE
            WHERE session_token = :token
        ");

        $stmt->execute([':token' => $token]);
    }

    // ================== دوال مساعدة ==================

    private function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function userExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function isIpRateLimited(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $timeLimit = date('Y-m-d H:i:s', time() - 900);

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM login_attempts
            WHERE ip_address = :ip
            AND attempted_at > :time_limit
            AND attempt_status = 'wrong_password'
        ");

        $stmt->execute([':ip' => $ip, ':time_limit' => $timeLimit]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return ($result['count'] ?? 0) >= ($this->config['max_login_attempts'] ?? 10);
    }

    private function logLoginAttempt(string $email): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (email, ip_address, user_agent)
            VALUES (:email, :ip, :ua)
        ");

        $stmt->execute([
            ':email' => $email,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function updateLoginAttempt(int $id, string $status): void
    {
        $stmt = $this->db->prepare("
            UPDATE login_attempts
            SET attempt_status = :status
            WHERE id = :id
        ");

        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    private function incrementFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET failed_login_attempts = COALESCE(failed_login_attempts, 0) + 1
            WHERE id = :id
        ");

        $stmt->execute([':id' => $userId]);

        $maxAttempts = $this->config['max_login_attempts'] ?? 5;
        
        $stmt = $this->db->prepare("
            UPDATE users
            SET account_locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            WHERE id = :id
            AND failed_login_attempts >= :max_attempts
        ");

        $stmt->execute([
            ':id' => $userId,
            ':max_attempts' => $maxAttempts
        ]);
    }

    private function resetFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET failed_login_attempts = 0,
                account_locked_until = NULL
            WHERE id = :id
        ");

        $stmt->execute([':id' => $userId]);
    }

    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET last_login_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([':id' => $userId]);
    }

    private function generateDeviceFingerprint(): string
    {
        $components = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''
        ];

        return hash('sha256', implode('|', $components));
    }

    private function delayResponse(): void
    {
        usleep(rand(1000000, 3000000));
    }

    private function genericErrorResponse(): array
    {
        return [
            'success' => false,
            'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
        ];
    }

    public function checkPasswordStrength(string $password, string $role): array
    {
        $minLength = $this->config['min_password_length'] ?? 8;

        if (strlen($password) < $minLength) {
            return [
                'valid' => false,
                'message' => "كلمة المرور يجب أن تكون $minLength أحرف على الأقل"
            ];
        }

        if ($role === 'admin' || $role === 'vendor') {
            if (!preg_match('/[A-Z]/', $password) ||
                !preg_match('/[a-z]/', $password) ||
                !preg_match('/[0-9]/', $password)) {
                return [
                    'valid' => false,
                    'message' => 'كلمة المرور يجب أن تحتوي على حروف كبيرة وصغيرة وأرقام'
                ];
            }
        }

        $commonPasswords = ['password', '123456', 'qwerty', 'password123', 'admin123'];
        if (in_array(strtolower($password), $commonPasswords)) {
            return [
                'valid' => false,
                'message' => 'كلمة المرور ضعيفة جدًا، الرجاء اختيار كلمة أقوى'
            ];
        }

        return ['valid' => true, 'message' => 'كلمة المرور قوية'];
    }

    private function verifyMFA(string $secret, string $code): bool
    {
        return strlen($code) === 6 && is_numeric($code);
    }

    private function verifyBackupCode(int $userId, string $code): bool
    {
        $stmt = $this->db->prepare("SELECT mfa_backup_codes FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($user['mfa_backup_codes'])) {
            return false;
        }

        $backupCodes = json_decode($user['mfa_backup_codes'], true);

        if (in_array($code, $backupCodes)) {
            $newCodes = array_diff($backupCodes, [$code]);
            $stmt = $this->db->prepare("UPDATE users SET mfa_backup_codes = :codes WHERE id = :id");
            $stmt->execute([':codes' => json_encode(array_values($newCodes)), ':id' => $userId]);

            return true;
        }

        return false;
    }

    public function logSecurityEvent(?int $userId, string $action, string $description): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO security_logs (user_id, action_type, description, ip_address, user_agent)
                VALUES (:user_id, :action, :description, :ip, :ua)
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (\PDOException $e) {
            error_log("فشل تسجيل حدث الأمان: " . $e->getMessage());
        }
    }
}
