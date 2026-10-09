<?php
ob_start();
session_start();

if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "الرجاء تسجيل الدخول أولاً";
    header("Location: login.php");
    exit();
}

$pageTitle = "إتمام الشراء";
include "includes/header.php";
include "includes/nav.php";
include "conect.php";

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

// جلب معلومات الكتاب
$stmt = $con->prepare("SELECT * FROM books WHERE id = ? AND status = 'متاح'");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if(!$book) {
    $_SESSION['error'] = "الكتاب غير متوفر حالياً";
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
    $card_holder = $_POST['card_holder'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $shipping_address = $_POST['shipping_address'];
    $shipping_phone = $_POST['shipping_phone'];
    
    // التحقق من صحة البيانات
    $errors = [];
    
    if(!preg_match('/^[0-9]{16}$/', $card_number)) {
        $errors[] = "رقم البطاقة غير صحيح (يجب أن يكون 16 رقم)";
    }
    
    if(empty($card_holder)) {
        $errors[] = "الرجاء إدخال اسم حامل البطاقة";
    }
    
    if(!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry_date)) {
        $errors[] = "تاريخ الانتهاء غير صحيح (صيغة: MM/YY)";
    }
    
    if(!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $errors[] = "رمز CVV غير صحيح";
    }
    
    if(empty($shipping_address)) {
        $errors[] = "الرجاء إدخال عنوان الشحن";
    }
    
    if(empty($shipping_phone)) {
        $errors[] = "الرجاء إدخال رقم الهاتف";
    }
    
    if(empty($errors)) {
        try {
            $con->beginTransaction();
            
            // التحقق مرة أخرى من توفر الكتاب
            if(!isBookAvailable($con, $book_id)) {
                throw new Exception("الكتاب غير متوفر حالياً");
            }
            
            // إنشاء رقم معاملة فريد
            $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
            
            // تسجيل عملية الدفع
            $stmt = $con->prepare("
                INSERT INTO payments (user_id, book_id, amount, card_number, card_holder, transaction_id, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $book_id,
                $book['price'],
                $card_number,
                $card_holder,
                $transaction_id
            ]);
            
            $payment_id = $con->lastInsertId();
            
            // إنشاء رقم طلب
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // تسجيل الطلب
            $stmt = $con->prepare("
                INSERT INTO orders (order_number, user_id, book_id, total_amount, shipping_address, order_status, payment_id) 
                VALUES (?, ?, ?, ?, ?, 'جاري المعالجة', ?)
            ");
            $stmt->execute([
                $order_number,
                $_SESSION['user_id'],
                $book_id,
                $book['price'],
                $shipping_address,
                $payment_id
            ]);
            
            // تحديث حالة الكتاب
            markBookAsSold($con, $book_id);
            
            $con->commit();
            
            $_SESSION['success'] = "تم شراء الكتاب بنجاح! رقم الطلب: " . $order_number;
            header("Location: purchase_success.php?order=" . $order_number);
            exit();
            
        } catch(Exception $e) {
            $con->rollBack();
            $error = "حدث خطأ: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">💳 إتمام عملية الشراء</h4>
                </div>
                <div class="card-body">
                    <!-- معلومات الكتاب -->
                    <div class="alert alert-info">
                        <h5>📖 معلومات الكتاب</h5>
                        <p><strong>العنوان:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
                        <p><strong>المؤلف:</strong> <?php echo htmlspecialchars($book['author'] ?? 'غير محدد'); ?></p>
                        <p><strong>السعر:</strong> <?php echo number_format($book['price'], 2); ?> ج.س</p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <h5 class="mb-3">💳 بيانات الدفع</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>رقم البطاقة</label>
                                <input type="text" name="card_number" class="form-control" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>اسم حامل البطاقة</label>
                                <input type="text" name="card_holder" class="form-control" 
                                       placeholder="كما يظهر على البطاقة" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>تاريخ الانتهاء (MM/YY)</label>
                                <input type="text" name="expiry_date" class="form-control" 
                                       placeholder="12/25" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>CVV</label>
                                <input type="password" name="cvv" class="form-control" 
                                       placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 mt-4">🚚 معلومات الشحن</h5>
                        
                        <div class="mb-3">
                            <label>عنوان الشحن</label>
                            <textarea name="shipping_address" class="form-control" rows="3" 
                                      placeholder="العنوان بالكامل (الشارع، المدينة، الرمز البريدي)" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>رقم الهاتف</label>
                            <input type="tel" name="shipping_phone" class="form-control" 
                                   placeholder="09xxxxxxxx" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                💳 تأكيد الدفع وشراء الكتاب
                            </button>
                            <a href="index.php" class="btn btn-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>