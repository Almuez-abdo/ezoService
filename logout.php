<?php
ob_start();
session_start();

// تسجيل الخروج
session_destroy();

// حذف جميع بيانات الجلسة
$_SESSION = array();

// حذف الكوكيز إذا وجدت
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>