<?php
// تفعيل عرض جميع الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ob_start();
session_start();

$pageTitle = "مكتبة عزو - الرئيسية";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
include "includes/app_data.php";

// جلب الأقسام
$stSec = $con->prepare("SELECT * FROM sections ORDER BY name");
$stSec->execute();
$sections = $stSec->fetchAll();

// جلب الكتب المتاحة
$available_books = getAvailableBooks($con, 12);

// إحصائيات سريعة للواجهة
$stCount = $con->query("SELECT COUNT(*) AS c FROM books WHERE status = 'متاح'");
$booksCount = $stCount ? (int)$stCount->fetch()['c'] : count($available_books);
$secCount = count($sections);
?>

<div class="container mt-4">
    <!-- رسائل التنبيه -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- البطل -->
    <div class="ez-hero">
        <h1>Ezzo Service — مكتبتك الشاملة</h1>
        <p>مكتبة عامة: إنجليزية وثقافية ودينية • قسم الشهادة السودانية • قسم جامعة السودان المفتوحة • طلب بحوث التخرج</p>
        <form class="ez-search d-flex gap-2 mt-3" action="books.php" method="get">
            <input type="search" name="q" class="form-control" placeholder="ابحث بعنوان الكتاب أو اسم المؤلف...">
            <button class="btn" type="submit">بحث</button>
        </form>
        <div class="ez-stats">
            <div><b>15+</b><span>كتاب عام</span></div>
            <div><b>934</b><span>ملف شهادة</span></div>
            <div><b>22</b><span>مادة</span></div>
            <div><b><?php echo $booksCount; ?>+</b><span>كتاب بسوق عزو</span></div>
        </div>
    </div>

    <!-- أقسام التطبيق -->
    <div class="row mt-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="library.php" class="text-decoration-none"><div class="card book-card h-100">
                <img src="<?php echo coverImg('gb-no1'); ?>" class="card-img-top" alt="المكتبة العامة">
                <div class="card-body text-center">
                <h5 class="card-title">المكتبة العامة</h5><p class="card-text text-muted">إنجليزية • ثقافية • دينية • روايات</p>
            </div></div></a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="sudani.php" class="text-decoration-none"><div class="card book-card h-100">
                <img src="<?php echo coverImg('su-arabic'); ?>" class="card-img-top" alt="الشهادة السودانية">
                <div class="card-body text-center">
                <h5 class="card-title">الشهادة السودانية</h5><p class="card-text text-muted">22 مادة • ملفات واختبارات</p>
            </div></div></a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="openuniv.php" class="text-decoration-none"><div class="card book-card h-100">
                <img src="<?php echo coverImg('ou-eco'); ?>" class="card-img-top" alt="الجامعة المفتوحة">
                <div class="card-body text-center">
                <h5 class="card-title">الجامعة المفتوحة</h5><p class="card-text text-muted">كليات • تخصصات • مستويات</p>
            </div></div></a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="thesis.php" class="text-decoration-none"><div class="card book-card h-100">
                <img src="layout/images/logo.png" class="card-img-top" alt="طلب بحث تخرج">
                <div class="card-body text-center">
                <h5 class="card-title">طلب بحث تخرج</h5><p class="card-text text-muted">استمارة + واتساب مباشر</p>
            </div></div></a>
        </div>
    </div>

    <!-- أقسام سريعة -->
    <div class="ez-pills">
        <a href="books.php" class="on">الكل</a>
        <?php foreach($sections as $section): ?>
            <a href="books.php?section=<?php echo $section['id']; ?>">
                <?php echo htmlspecialchars($section['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- قسم عرض الكتب -->
        <div class="col-lg-9 col-md-8">
            <h3 class="ez-sec-title">سوق عزو — أحدث الكتب المتاحة للبيع والاستبدال</h3>

            <?php if(count($available_books) > 0): ?>
                <div class="row">
                    <?php foreach($available_books as $book): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card book-card h-100">
                                <img src="<?php echo htmlspecialchars(getBookImage($book)); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <small><?php echo htmlspecialchars($book['author'] ?? 'غير محدد'); ?> · <?php echo htmlspecialchars($book['section_name'] ?? ''); ?></small>
                                    </p>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars(substr($book['description'] ?? '', 0, 80)); ?>...
                                    </p>
                                    <p class="ez-price"><?php echo number_format($book['price'], 2); ?> ج.س</p>
                                    <p class="card-text">
                                        <small class="text-muted">الحالة: <?php echo htmlspecialchars($book['book_condition'] ?? ''); ?></small>
                                    </p>
                                    <div class="d-grid gap-2 mt-auto">
                                        <button onclick="buyBook(<?php echo $book['id']; ?>, <?php echo $book['price']; ?>)"
                                                class="btn-ez">
                                            اشتر الآن
                                        </button>
                                        <button onclick="requestExchange(<?php echo $book['id']; ?>)"
                                                class="btn-ez-outline">
                                            طلب استبدال
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    لا توجد كتب متاحة حالياً. <a href="sell_book.php">كن أول من يضيف كتاباً!</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- القائمة الجانبية -->
        <div class="col-lg-3 col-md-4 ez-side">
            <div class="card mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0">الأقسام</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach($sections as $section): ?>
                            <li class="mb-2">
                                <a href="books.php?section=<?php echo $section['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($section['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header green text-white">
                    <h5 class="mb-0">روابط سريعة</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="sell_book.php" class="text-decoration-none">بيع كتاب جديد</a></li>
                        <li class="mb-2"><a href="my_books.php" class="text-decoration-none">كتبي</a></li>
                        <li class="mb-2"><a href="exchange_requests.php" class="text-decoration-none">طلبات الاستبدال</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li class="mb-2"><a href="logout.php" class="text-decoration-none text-danger">تسجيل خروج</a></li>
                        <?php else: ?>
                            <li class="mb-2"><a href="login.php" class="text-decoration-none">تسجيل دخول</a></li>
                            <li class="mb-2"><a href="register.php" class="text-decoration-none">حساب جديد</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function buyBook(bookId, price) {
    <?php if(!isset($_SESSION['user_id'])): ?>
        alert('الرجاء تسجيل الدخول أولاً');
        window.location.href = 'login.php';
        return false;
    <?php else: ?>
        if(confirm('تأكيد شراء الكتاب بقيمة ' + price + ' ج.س؟')) {
            window.location.href = 'checkout.php?book_id=' + bookId;
        }
    <?php endif; ?>
}

function requestExchange(bookId) {
    <?php if(!isset($_SESSION['user_id'])): ?>
        alert('الرجاء تسجيل الدخول أولاً');
        window.location.href = 'login.php';
        return false;
    <?php else: ?>
        window.location.href = 'request_exchange.php?book_id=' + bookId;
    <?php endif; ?>
}
</script>

<?php
include "includes/footer.php";
ob_end_flush();
?>
