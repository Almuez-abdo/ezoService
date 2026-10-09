<?php
// includes/security.php

// إنشاء CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// التحقق من CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// تحديد عدد محاولات تسجيل الدخول الفاشلة
function checkLoginAttempts($con, $email, $ip) {
    $timeframe = 15; // دقائق
    $maxAttempts = 5; // أقصى عدد من المحاولات
    
    $stmt = $con->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE email = ? 
        AND ip_address = ? 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmt->execute([$email, $ip, $timeframe]);
    $result = $stmt->fetch();
    
    return $result['attempts'] >= $maxAttempts;
}

// تسجيل محاولة فاشلة
function logFailedAttempt($con, $email, $ip) {
    $stmt = $con->prepare("
        INSERT INTO login_attempts (email, ip_address, attempt_time) 
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$email, $ip]);
}

// مسح المحاولات الفاشلة بعد نجاح تسجيل الدخول
function clearLoginAttempts($con, $email, $ip) {
    $stmt = $con->prepare("
        DELETE FROM login_attempts 
        WHERE email = ? OR ip_address = ?
    ");
    $stmt->execute([$email, $ip]);
}

// تسجيل خروج آمن
function secureLogout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}

// منع XSS
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// إنشاء رمز إعادة تعيين كلمة المرور
function generateResetToken($con, $email) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $con->prepare("
        UPDATE users 
        SET reset_token = ?, reset_expires = ? 
        WHERE email = ?
    ");
    $stmt->execute([$token, $expires, $email]);
    
    return $token;
}
?>