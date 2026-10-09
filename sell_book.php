<?php
ob_start();
session_start();

if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "الرجاء تسجيل الدخول أولاً";
    header("Location: login.php");
    exit();
}

$pageTitle = "بيع كتاب جديد";
include "includes/header.php";
include "includes/nav.php";
include "conect.php";

// جلب الأقسام
$stmt = $con->prepare("SELECT * FROM sections ORDER BY name");
$stmt->execute();
$sections = $stmt->fetchAll();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $section_id = $_POST['section_id'];
    $book_condition = $_POST['book_condition'];
    
    // التحقق من صحة البيانات
    $errors = [];
    
    if(empty($title)) $errors[] = "الرجاء إدخال عنوان الكتاب";
    if(empty($price) || !is_numeric($price)) $errors[] = "الرجاء إدخال سعر صحيح";
    if(empty($section_id)) $errors[] = "الرجاء اختيار القسم";
    
    // رفع الصورة
    $image_path = '';
    if(isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $upload_dir = 'uploads/books/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image_name = 'book_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if(move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_dir . $image_name)) {
                $image_path = $upload_dir . $image_name;
            } else {
                $errors[] = "خطأ في رفع الصورة";
            }
        } else {
            $errors[] = "الصورة يجب أن تكون JPG, PNG أو GIF";
        }
    } else {
        $errors[] = "الرجاء رفع صورة للكتاب";
    }
    
    if(empty($errors)) {
        $data = [
            'title' => $title,
            'author' => $author,
            'description' => $description,
            'price' => $price,
            'section_id' => $section_id,
            'seller_id' => $_SESSION['user_id'],
            'book_condition' => $book_condition
        ];
        
        if(addBook($con, $data, $image_path)) {
            $_SESSION['success'] = "تم إضافة كتابك بنجاح! سيظهر بعد مراجعة الإدارة.";
            header("Location: my_books.php");
            exit();
        } else {
            $error = "حدث خطأ في إضافة الكتاب";
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
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">➕ إضافة كتاب جديد للبيع</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>عنوان الكتاب *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>اسم المؤلف</label>
                            <input type="text" name="author" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label>القسم *</label>
                            <select name="section_id" class="form-control" required>
                                <option value="">اختر القسم</option>
                                <?php foreach($sections as $section): ?>
                                    <option value="<?php echo $section['id']; ?>">
                                        <?php echo htmlspecialchars($section['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>السعر (ج.س) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>حالة الكتاب *</label>
                            <select name="book_condition" class="form-control" required>
                                <option value="جديد">جديد</option>
                                <option value="مثل الجديد">مثل الجديد</option>
                                <option value="جيد">جيد</option>
                                <option value="مقبول">مقبول</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>صورة الكتاب *</label>
                            <input type="file" name="book_image" class="form-control" accept="image/*" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>وصف الكتاب</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="وصف مختصر عن الكتاب"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">إضافة الكتاب</button>
                            <a href="index.php" class="btn btn-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>