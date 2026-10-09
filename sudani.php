<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
$pageTitle = "قسم الشهادة السودانية - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
include "includes/app_data.php";
$groups = ['أساسية'=>'المواد الأساسية','علمية'=>'القسم العلمي','أدبية'=>'القسم الأدبي','اختيارية'=>'اختيارية'];
?>
<div class="container mt-4">
    <h3 class="ez-sec-title">قسم الشهادة السودانية</h3>
    <p class="text-muted">كل مقررك بين يديك — 22 مادة • اختر المادة لعرض الكتب والمذكرات والامتحانات</p>
    <?php foreach($groups as $key=>$label): ?>
        <h4 class="mt-4 mb-3"><?php echo $label; ?></h4>
        <div class="row">
            <?php foreach($APP_SUBJECTS as $s): if($s['section']!==$key) continue; ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <a href="sudani_subject.php?id=<?php echo $s['id']; ?>" class="text-decoration-none">
                    <div class="card book-card h-100">
                        <img src="<?php echo coverImg('su-'.$s['id']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($s['name']); ?>" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">ملفات <?php echo htmlspecialchars($s['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo $s['files']; ?> ملف PDF</p>
                            <span class="btn-ez-outline d-inline-block px-3">عرض الملفات + اختبر نفسك</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
