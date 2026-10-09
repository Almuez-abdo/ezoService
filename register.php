<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);  // ✅ إخفاء الأخطاء
ini_set('log_errors', 1);      // ✅ تسجيل الأخطاء

ob_start();
session_start();

// توليد CSRF token
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

try {
    include 'includes/conect.php';
} catch(Exception $e) {
    error_log($e->getMessage());
    die("عذراً، حدث خطأ في النظام. الرجاء المحاولة لاحقاً.");
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ✅ التحقق من CSRF token
    if(!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "خطأ في التحقق من الأمان. يرجى تحديث الصفحة.";
    } else {
        
        // ✅ تنظيف المدخلات
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $full_name = htmlspecialchars(trim($_POST['full_name']), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        // ✅ التحقق من صحة البيانات
        if(empty($username)) $errors[] = "اسم المستخدم مطلوب";
        if(strlen($username) < 3) $errors[] = "اسم المستخدم يجب أن يكون 3 أحرف على الأقل";
        
        if(empty($email)) $errors[] = "البريد الإلكتروني مطلوب";
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "البريد الإلكتروني غير صالح";
        
        if(empty($full_name)) $errors[] = "الاسم الكامل مطلوب";
        
        if(empty($password)) $errors[] = "كلمة المرور مطلوبة";
        if($password != $confirm_password) $errors[] = "كلمة المرور غير متطابقة";
        
        // ✅ سياسة كلمة مرور قوية
        if(strlen($password) < 8) $errors[] = "كلمة المرور يجب أن تكون 8 أحرف على الأقل";
        if(!preg_match('/[A-Z]/', $password)) $errors[] = "كلمة المرور يجب أن تحتوي على حرف كبير (A-Z)";
        if(!preg_match('/[a-z]/', $password)) $errors[] = "كلمة المرور يجب أن تحتوي على حرف صغير (a-z)";
        if(!preg_match('/[0-9]/', $password)) $errors[] = "كلمة المرور يجب أن تحتوي على رقم (0-9)";
        if(!preg_match('/[!@#$%^&*]/', $password)) $errors[] = "كلمة المرور يجب أن تحتوي على رمز خاص (!@#$%^&*)";
        
        if(empty($errors)) {
            try {
                // ✅ استخدام معاملة (Transaction) لضمان التكامل
                $con->beginTransaction();
                
                // التحقق من وجود البريد
                $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if($stmt->fetch()) {
                    $errors[] = "البريد الإلكتروني موجود بالفعل";
                }
                
                // التحقق من وجود اسم المستخدم
                $stmt = $con->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if($stmt->fetch()) {
                    $errors[] = "اسم المستخدم موجود بالفعل";
                }
                
                if(empty($errors)) {
                    // ✅ تشفير قوي (cost 12)
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    $stmt = $con->prepare("
                        INSERT INTO users (username, email, password, full_name, phone, user_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'user', NOW())
                    ");
                    
                    if($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                        $con->commit();
                        $success = "تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
                        
                        // ✅ تسجيل الحدث
                        error_log("New user registered: $email from IP: {$_SERVER['REMOTE_ADDR']}");
                        
                        // ✅ توجيه آمن
                        header("Location: login.php?registered=1");
                        exit();
                    } else {
                        $con->rollBack();
                        $error = "حدث خطأ في إنشاء الحساب";
                    }
                } else {
                    $con->rollBack();
                }
            } catch(Exception $e) {
                $con->rollBack();
                error_log("Registration error: " . $e->getMessage());
                $error = "حدث خطأ في النظام. الرجاء المحاولة لاحقاً.";
            }
        }
        
        if(!empty($errors)) {
            $error = implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - مكتبة عزو</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .register-card {
            max-width: 500px;
            margin: auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .register-body {
            padding: 30px;
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            width: 100%;
            color: white;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="register-header">
                <h3>📚 مكتبة عزو</h3>
                <p>إنشاء حساب جديد</p>
            </div>
            <div class="register-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label>اسم المستخدم</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required pattern="[A-Za-z0-9_]{3,20}" 
                               title="3-20 حرف، أحرف وأرقام و_ فقط">
                    </div>
                    
                    <div class="mb-3">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label>الاسم الكامل</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label>رقم الهاتف</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <div class="password-strength text-muted" id="passwordStrength"></div>
                        <small class="text-muted">8 أحرف على الأقل + حرف كبير + رقم + رمز خاص</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>تأكيد كلمة المرور</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-register">إنشاء حساب</button>
                    
                    <div class="text-center mt-3">
                        <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
                        <p><a href="index.php">العودة إلى الرئيسية</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // ✅ التحقق من قوة كلمة المرور في المتصفح
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('passwordStrength');
        
        let strength = 0;
        if(password.length >= 8) strength++;
        if(password.match(/[A-Z]/)) strength++;
        if(password.match(/[a-z]/)) strength++;
        if(password.match(/[0-9]/)) strength++;
        if(password.match(/[!@#$%^&*]/)) strength++;
        
        if(strength <= 2) strengthDiv.innerHTML = 'ضعيفة 🔴';
        else if(strength <= 4) strengthDiv.innerHTML = 'متوسطة 🟡';
        else strengthDiv.innerHTML = 'قوية 🟢';
        
        if(strength <= 2) strengthDiv.style.color = 'red';
        else if(strength <= 4) strengthDiv.style.color = 'orange';
        else strengthDiv.style.color = 'green';
    });
    </script>
</body>
</html>

<?php ob_end_flush(); ?>