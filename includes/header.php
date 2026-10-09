<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="layout/css/bootstrap.min.css">
  <link rel="stylesheet" href="layout/css/front.css?v=2">
  <title><?php echo getTitle(); ?></title>
</head>
<body>
<header class="ez-topbar">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <a href="index.php" class="ez-brand text-white text-decoration-none">
            <img src="layout/images/logo.png" alt="مكتبة عزو">
            <span>
                <h3>مكتبة عزو</h3>
                <small>EZZO SERVICE — مكتبة عامة • شهادة سودانية • جامعة مفتوحة</small>
            </span>
        </a>
        <form class="ez-search d-flex gap-2" action="books.php" method="get">
            <input type="search" name="q" class="form-control" placeholder="ابحث عن كتاب أو مؤلف..."
                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button class="btn" type="submit">بحث</button>
        </form>
        <div class="ez-auth d-flex gap-2">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="my_books.php" class="btn btn-light">كتبي</a>
                <a href="logout.php" class="btn btn-outline-light">خروج</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light">دخول</a>
                <a href="register.php" class="btn btn-dark">حساب جديد</a>
            <?php endif; ?>
        </div>
    </div>
</header>
