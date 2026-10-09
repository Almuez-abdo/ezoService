<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
$pageTitle = "جامعة السودان المفتوحة - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
include "includes/app_data.php";
?>
<div class="container mt-4">
    <h3 class="ez-sec-title">جامعة السودان المفتوحة</h3>
    <p class="text-muted">اختر الكلية ثم التخصص ثم المستوى — لكل مادة: كتاب وملخص وامتحان سابق</p>
    <div class="row">
        <?php foreach($APP_OU_COLLEGES as $c):
            $majors = array_values(array_filter($APP_OU_MAJORS, fn($m)=>$m['college']===$c['id']));
            $subs = 0; foreach($majors as $m) $subs += count(array_filter($APP_OU_SUBJECTS, fn($s)=>$s['major']===$m['id']));
        ?>
        <div class="col-md-4 col-sm-6 mb-3">
            <a href="openuniv_college.php?c=<?php echo $c['id']; ?>" class="text-decoration-none">
                <div class="card book-card h-100">
                    <img src="<?php echo coverImg('ou-'.$c['id']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($c['name']); ?>" loading="lazy">
                    <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($c['name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($c['desc']); ?></p>
                    <p class="card-text"><?php echo count($majors); ?> تخصصات • <?php echo $subs; ?> مواد</p>
                </div></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
