<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // إخفاء الأخطاء في الإنتاج
ini_set('log_errors', 1); // تسجيل الأخطاء في ملف السجل

ob_start();
session_start();

// تضمين ملفات الأمان
require_once 'includes/security.php';
require_once 'includes/conect.php';

// التحقق من وجود جلسة نشطة
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$showCaptcha = false;

// الحصول على IP الحقيقي للمستخدم
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$userIP = getClientIP();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. التحقق من CSRF token
    if(!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "خطأ في التحقق من الأمان. يرجى تحديث الصفحة والمحاولة مرة أخرى.";
    } else {
        
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // التحقق من صحة البريد الإلكتروني
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "البريد الإلكتروني غير صالح";
        } elseif(empty($password)) {
            $error = "الرجاء إدخال كلمة المرور";
        } else {
            
            // 2. التحقق من عدد محاولات تسجيل الدخول
            if(checkLoginAttempts($con, $email, $userIP)) {
                $error = "لقد تجاوزت عدد المحاولات المسموح بها. الرجاء المحاولة بعد 15 دقيقة.";
            } else {
                
                // 3. البحث عن المستخدم
                try {
                    $stmt = $con->prepare("
                        SELECT id, full_name, email, password, user_type, 
                               reset_token, reset_expires 
                        FROM users 
                        WHERE email = ? 
                    ");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if($user) {
                        // التحقق من أن الحساب غير مقفل
                        if($user['reset_token'] == 1 && strtotime($user['reset_expires']) > time()) {
                            $error = "الحساب مقفل. الرجاء المحاولة بعد " . date('H:i', strtotime($user['reset_expires'])) . " ساعة";
                        } else {
                            
                            // 4. التحقق من كلمة المرور باستخدام password_verify
                            if(password_verify($password, $user['password'])) {
                                
                                // تسجيل دخول ناجح
                                clearLoginAttempts($con, $email, $userIP);
                                
                                // تجديد Session ID للحماية من Session Fixation
                                session_regenerate_id(true);
                                
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['user_name'] = $user['full_name'];
                                $_SESSION['user_email'] = $user['email'];
                                $_SESSION['user_type'] = $user['user_type'];
                                $_SESSION['login_time'] = time();
                                $_SESSION['ip_address'] = $userIP;
                                
                                // تسجيل في سجل الأمان
                                error_log("تسجيل دخول ناجح للمستخدم: {$user['email']} من IP: $userIP");
                                if($user['user_type'] == 'admin'){
                                    header("Location: admin/index.php");
                                    exit();
                                }else{
                                header("Location: index.php");
                                exit();
                                }
                                
                            } else {
                                // كلمة مرور خاطئة
                                logFailedAttempt($con, $email, $userIP);
                                $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
                                
                                // التحقق مما إذا كان يجب إظهار CAPTCHA بعد عدة محاولات
                                $attempts = checkLoginAttempts($con, $email, $userIP);
                                if($attempts >= 3) {
                                    $showCaptcha = true;
                                }
                            }
                        }
                    } else {
                        // إخفاء أن البريد الإلكتروني غير موجود (نفس رسالة الخطأ لأمان أفضل)
                        logFailedAttempt($con, $email, $userIP);
                        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
                        
                        if(checkLoginAttempts($con, $email, $userIP) >= 3) {
                            $showCaptcha = true;
                        }
                    }
                    
                } catch(Exception $e) {
                    echo $e->getMessage();
                    // $error = "حدث خطأ في النظام. الرجاء المحاولة لاحقاً.";
                }
            }
        }
    }
}

// إنشاء CSRF token جديد
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مكتبة عزو</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .login-card {
            max-width: 400px;
            margin: auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            width: 100%;
            color: white;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .captcha-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
    </style>
    <!-- Google reCAPTCHA v2 -->
    <?php if($showCaptcha): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <h3>📚 مكتبة عزو</h3>
                <p>تسجيل الدخول إلى حسابك</p>
            </div>
            <div class="login-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">❌ <?php echo sanitizeOutput($error); ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">✅ <?php echo sanitizeOutput($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo isset($_POST['email']) ? sanitizeOutput($_POST['email']) : ''; ?>" 
                               required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <?php if($showCaptcha): ?>
                    <div class="mb-3 captcha-container">
                        <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
                        <small class="text-muted">ظهرت عدة محاولات فاشلة، الرجاء تأكيد أنك لست روبوتاً</small>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn-login">تسجيل الدخول</button>
                    
                    <div class="text-center mt-3">
                        <p><a href="forgot_password.php">نسيت كلمة المرور؟</a></p>
                        <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
                        <p><a href="index.php">العودة إلى الرئيسية</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- إضافة JavaScript لحماية إضافية -->
    <script>
    // منع إرسال النموذج مرتين
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if(submitBtn.disabled) {
            e.preventDefault();
        } else {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'جاري تسجيل الدخول...';
        }
    });
    
    // حذف رسائل الخطأ بعد 5 ثوانٍ
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 1000);
        });
    }, 5000);
    </script>
</body>
</html>

<?php ob_end_flush(); ?>