<?php
// includes/nav.php - القائمة العلوية (هوية تطبيق عزو)
$nav_page = basename($_SERVER['PHP_SELF']);
function navActive($f){ global $nav_page; return $nav_page === $f ? 'active' : ''; }
?>
<nav class="navbar navbar-expand-lg ez-nav sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">مكتبة عزو</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?php echo navActive('index.php'); ?>" href="index.php">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link <?php echo navActive('library.php'); ?>" href="library.php">المكتبة العامة</a></li>
                <li class="nav-item"><a class="nav-link <?php echo navActive('sudani.php'); ?>" href="sudani.php">الشهادة السودانية</a></li>
                <li class="nav-item"><a class="nav-link <?php echo navActive('openuniv.php'); ?>" href="openuniv.php">الجامعة المفتوحة</a></li>
                <li class="nav-item"><a class="nav-link <?php echo navActive('thesis.php'); ?>" href="thesis.php">طلب بحث</a></li>
                <li class="nav-item"><a class="nav-link <?php echo navActive('books.php'); ?>" href="books.php">سوق الكتب</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('sell_book.php'); ?>" href="sell_book.php">بيع كتاب</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('my_books.php'); ?>" href="my_books.php">كتبي</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'حسابي'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">حسابي</a></li>
                            <li><a class="dropdown-item" href="my_orders.php">طلباتي</a></li>
                            <li><a class="dropdown-item" href="my_books.php">كتبي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">تسجيل خروج</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('login.php'); ?>" href="login.php">دخول</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo navActive('register.php'); ?>" href="register.php">حساب جديد</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
