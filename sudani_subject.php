<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include "includes/func/function.php";
include "includes/app_data.php";
$id = $_GET['id'] ?? 'arabic';
$name = appSubjectName($id);
$pageTitle = "ملفات $name - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
$files = sudaniFiles($id);
$types = [];
foreach($files as $f) $types[$f['type']][] = $f;
?>
<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="sudani.php">الشهادة السودانية</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($name); ?></li>
    </ol></nav>
    <div class="ez-hero">
        <div class="row align-items-center">
            <div class="col-md-3 mb-3 mb-md-0"><img src="<?php echo coverImg('su-'.$id); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($name); ?>"></div>
            <div class="col-md-9">
                <h1><?php echo htmlspecialchars($name); ?></h1>
                <p>كتب ومذكرات وأوراق عمل وامتحانات سابقة وإجابات نموذجية</p>
                <a href="quiz.php?subject=<?php echo urlencode($id); ?>" class="btn btn-warning fw-bold mt-2">اختبر نفسك الآن</a>
            </div>
        </div>
    </div>
    <?php foreach($types as $type=>$items): ?>
        <h4 class="mt-4 mb-3"><?php echo htmlspecialchars($type); ?> • <?php echo count($items); ?></h4>
        <div class="row">
            <?php foreach($items as $f): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card book-card h-100"><div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($f['title']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($f['type']); ?> • <?php echo $f['pages']; ?> صفحة</p>
                    <span class="badge bg-danger">PDF</span>
                    <span class="badge bg-secondary">عرض تجريبي</span>
                </div></div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
