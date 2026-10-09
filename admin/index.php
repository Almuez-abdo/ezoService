<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ob_start();
session_start();


// التحقق من صلاحيات الأدمن
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "لوحة التحكم - الأدمن";
include "includes/temp/header.php";
include "includes/temp/conect.php";

// إحصائيات عامة
$stats = [];

// عدد المستخدمين
$stmt = $con->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch()['count'];

// عدد الكتب
$stmt = $con->query("SELECT COUNT(*) as count FROM books");
$stats['books'] = $stmt->fetch()['count'];

// عدد الكتب المتاحة
$stmt = $con->query("SELECT COUNT(*) as count FROM books WHERE status = 'متاح'");
$stats['available_books'] = $stmt->fetch()['count'];

// عدد الطلبات
$stmt = $con->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch()['count'];

// عدد طلبات الاستبدال
$stmt = $con->query("SELECT COUNT(*) as count FROM exchange_images WHERE status = 'pending'");
$stats['pending_exchanges'] = $stmt->fetch()['count'];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>مرحباً <?php echo $_SESSION['user_name']; ?></h2>
            <p>هذه هي لوحة تحكم الأدمن</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">المستخدمين</h5>
                    <h2><?php echo $stats['users']; ?></h2>
                    <a href="users.php" class="text-white">عرض الكل →</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">جميع الكتب</h5>
                    <h2><?php echo $stats['books']; ?></h2>
                    <a href="books.php" class="text-white">عرض الكل →</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">الكتب المتاحة</h5>
                    <h2><?php echo $stats['available_books']; ?></h2>
                    <a href="books.php?status=available" class="text-white">عرض الكل →</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">الطلبات</h5>
                    <h2><?php echo $stats['orders']; ?></h2>
                    <a href="orders.php" class="text-white">عرض الكل →</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5>طلبات الاستبدال المعلقة</h5>
                </div>
                <div class="card-body">
                    <h2><?php echo $stats['pending_exchanges']; ?></h2>
                    <a href="exchanges.php" class="btn btn-danger">مراجعة الطلبات</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5>آخر النشاطات</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php
                        $stmt = $con->query("
                            SELECT * FROM users 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        while($user = $stmt->fetch()):
                        ?>
                            <li>📝 مستخدم جديد: <?php echo $user['full_name']; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>