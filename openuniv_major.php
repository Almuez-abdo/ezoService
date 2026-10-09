<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include "includes/func/function.php";
include "includes/app_data.php";
$mid = $_GET['m'] ?? 'cs1';
$major = null; foreach($APP_OU_MAJORS as $m) if($m['id']===$mid) $major=$m;
if(!$major){ header('Location: openuniv.php'); exit(); }
$college = null; foreach($APP_OU_COLLEGES as $c) if($c['id']===$major['college']) $college=$c;
$pageTitle = $major['name']." - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
$subs = array_values(array_filter($APP_OU_SUBJECTS, fn($s)=>$s['major']===$mid));
$levels = ['المستوى الأول','المستوى الثاني','المستوى الثالث','المستوى الرابع'];
?>
<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="openuniv.php">الجامعة المفتوحة</a></li>
        <li class="breadcrumb-item"><a href="openuniv_college.php?c=<?php echo $major['college']; ?>"><?php echo htmlspecialchars($college['name']); ?></a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($major['name']); ?></li>
    </ol></nav>
    <h3 class="ez-sec-title"><?php echo htmlspecialchars($major['name']); ?></h3>
    <?php foreach($levels as $lv):
        $items = array_values(array_filter($subs, fn($s)=>$s['level']===$lv));
        if(!$items) continue;
    ?>
        <h4 class="mt-4 mb-3"><?php echo $lv; ?> • <?php echo count($items); ?></h4>
        <?php foreach($items as $s): $files = ouFiles($s['title']); ?>
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><?php echo htmlspecialchars($s['title']); ?></h5>
                <a href="quiz.php?subject=<?php echo urlencode($s['id']); ?>" class="btn-ez-outline px-3 text-decoration-none">اختبر نفسك</a>
            </div>
            <div class="row mt-3">
                <?php foreach($files as $f): ?>
                <div class="col-md-4 mb-2">
                    <div class="border rounded p-2">
                        <b><?php echo htmlspecialchars($f['title']); ?></b><br>
                        <small class="text-muted"><?php echo htmlspecialchars($f['type']); ?> • <?php echo $f['pages']; ?> صفحة</small>
                        <span class="badge bg-danger">PDF</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
