<?php
ob_start();
session_start();

if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "الرجاء تسجيل الدخول أولاً";
    header("Location: login.php");
    exit();
}

$pageTitle = "طلب استبدال كتاب";
include "includes/header.php";
include "includes/nav.php";
include "conect.php";

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

// جلب معلومات الكتاب المطلوب
$stmt = $con->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$requested_book = $stmt->fetch();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_title = $_POST['book_title'];
    
    // التحقق من رفع الصور
    if(isset($_FILES['front_image']) && isset($_FILES['back_image'])) {
        $front_image = $_FILES['front_image'];
        $back_image = $_FILES['back_image'];
        
        $upload_errors = [];
        
        // التحقق من صحة الصور
        if($front_image['error'] != 0) {
            $upload_errors[] = "خطأ في رفع الصورة الأمامية";
        }
        
        if($back_image['error'] != 0) {
            $upload_errors[] = "خطأ في رفع الصورة الخلفية";
        }
        
        // التحقق من نوع الملف
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $front_ext = strtolower(pathinfo($front_image['name'], PATHINFO_EXTENSION));
        $back_ext = strtolower(pathinfo($back_image['name'], PATHINFO_EXTENSION));
        
        if(!in_array($front_ext, $allowed)) {
            $upload_errors[] = "الصورة الأمامية يجب أن تكون JPG, PNG أو GIF";
        }
        
        if(!in_array($back_ext, $allowed)) {
            $upload_errors[] = "الصورة الخلفية يجب أن تكون JPG, PNG أو GIF";
        }
        
        if(empty($upload_errors)) {
            // إنشاء مجلد إذا لم يكن موجوداً
            $upload_dir = 'uploads/exchanges/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // رفع الصور
            $front_name = 'front_' . time() . '_' . rand(1000, 9999) . '.' . $front_ext;
            $back_name = 'back_' . time() . '_' . rand(1000, 9999) . '.' . $back_ext;
            
            if(move_uploaded_file($front_image['tmp_name'], $upload_dir . $front_name) && 
               move_uploaded_file($back_image['tmp_name'], $upload_dir . $back_name)) {
                
                // حفظ طلب الاستبدال
                $stmt = $con->prepare("
                    INSERT INTO exchange_images (user_id, book_title, front_image, back_image, status) 
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                
                if($stmt->execute([$_SESSION['user_id'], $book_title, $upload_dir . $front_name, $upload_dir . $back_name])) {
                    $_SESSION['success'] = "تم إرسال طلب الاستبدال بنجاح، سيتم مراجعته من قبل الإدارة";
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "حدث خطأ في حفظ الطلب";
                }
            } else {
                $error = "حدث خطأ في رفع الصور";
            }
        } else {
            $error = implode("<br>", $upload_errors);
        }
    } else {
        $error = "الرجاء رفع صور الكتاب (الأمامية والخلفية)";
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">🔄 طلب استبدال كتاب</h4>
                </div>
                <div class="card-body">
                    <?php if($book_id > 0 && $requested_book): ?>
                        <div class="alert alert-info">
                            <h5>الكتاب المطلوب استبداله:</h5>
                            <p><strong><?php echo htmlspecialchars($requested_book['title']); ?></strong></p>
                            <p>السعر: <?php echo number_format($requested_book['price'], 2); ?> ج.س</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>عنوان الكتاب الذي تريد استبداله</label>
                            <input type="text" name="book_title" class="form-control" 
                                   value="<?php echo $requested_book ? htmlspecialchars($requested_book['title']) : ''; ?>"
                                   required>
                            <small class="text-muted">أدخل عنوان الكتاب الذي تريد تقديمه للاستبدال</small>
                        </div>
                        
                        <div class="mb-3">
                            <label>صورة الكتاب من الأمام</label>
                            <input type="file" name="front_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">يرجى رفع صورة واضحة للغلاف الأمامي</small>
                        </div>
                        
                        <div class="mb-3">
                            <label>صورة الكتاب من الخلف</label>
                            <input type="file" name="back_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">يرجى رفع صورة واضحة للغلاف الخلفي</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>ملاحظة:</strong> سيتم مراجعة طلبك من قبل الإدارة، وسيتم التواصل معك عبر البريد الإلكتروني.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">إرسال طلب الاستبدال</button>
                            <a href="index.php" class="btn btn-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>