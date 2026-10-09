<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
$pageTitle = "طلب بحث تخرج - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
include "includes/app_data.php";
// جدول الطلبات (ينشأ تلقائيا)
$con->exec("CREATE TABLE IF NOT EXISTS thesis_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL, phone VARCHAR(40) NOT NULL,
    university VARCHAR(150) DEFAULT '', title VARCHAR(255) NOT NULL,
    details TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$done = false; $err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??'');
    $uni=trim($_POST['university']??''); $title=trim($_POST['title']??''); $details=trim($_POST['details']??'');
    if($name===''||$phone===''||$title===''){ $err='أكمل الاسم والهاتف وعنوان البحث'; }
    else{
        $st=$con->prepare("INSERT INTO thesis_requests (name,phone,university,title,details) VALUES (?,?,?,?,?)");
        $st->execute([$name,$phone,$uni,$title,$details]);
        $done=true;
    }
}
$my = $con->query("SELECT * FROM thesis_requests ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<div class="container mt-4" style="max-width:720px">
    <h3 class="ez-sec-title">طلب بحث تخرج</h3>
    <p class="text-muted">املأ الاستمارة وسنتواصل معك عبر الهاتف / واتساب <?php echo ezPhoneHtml(); ?></p>
    <?php if($done): ?><div class="alert alert-success">تم استلام طلبك — سنتواصل معك قريبا</div><?php endif; ?>
    <?php if($err!==''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <div class="card"><div class="card-body">
        <form method="post">
            <div class="mb-3"><label class="form-label">الاسم الكامل</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">رقم الهاتف / واتساب</label><input name="phone" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">الجامعة / الكلية (اختياري)</label><input name="university" class="form-control"></div>
            <div class="mb-3"><label class="form-label">عنوان البحث</label><input name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">تفاصيل إضافية (اختياري)</label><textarea name="details" class="form-control" rows="3"></textarea></div>
            <button class="btn-ez w-100">إرسال الطلب</button>
        </form>
        <p class="text-muted mt-3 mb-0">أو تواصل واتساب مباشرة: <?php echo ezPhoneHtml(); ?></p>
    </div></div>
    <?php if($my): ?>
    <div class="card mt-3"><div class="card-body">
        <h5>أحدث الطلبات</h5>
        <?php foreach($my as $r): ?>
            <div class="border-bottom py-2"><small class="text-muted"><?php echo htmlspecialchars($r['created_at']); ?></small><br>
            <?php echo htmlspecialchars($r['name']); ?> — <?php echo htmlspecialchars($r['title']); ?></div>
        <?php endforeach; ?>
    </div></div>
    <?php endif; ?>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
