<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include "includes/func/function.php";
include "includes/app_data.php";
$cid = $_GET['c'] ?? 'eco';
$college = null; foreach($APP_OU_COLLEGES as $c) if($c['id']===$cid) $college=$c;
if(!$college){ header('Location: openuniv.php'); exit(); }
$pageTitle = $college['name']." - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
$majors = array_values(array_filter($APP_OU_MAJORS, fn($m)=>$m['college']===$cid));
?>
<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="openuniv.php">الجامعة المفتوحة</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($college['name']); ?></li>
    </ol></nav>
    <div class="row align-items-center mb-3">
        <div class="col-md-3 mb-3 mb-md-0"><img src="<?php echo coverImg('ou-'.$college['id']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($college['name']); ?>"></div>
        <div class="col-md-9">
            <h3 class="ez-sec-title"><?php echo htmlspecialchars($college['name']); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($college['desc']); ?></p>
        </div>
    </div>
    <div class="row">
        <?php foreach($majors as $m):
            $subs = array_values(array_filter($APP_OU_SUBJECTS, fn($s)=>$s['major']===$m['id']));
        ?>
        <div class="col-md-6 mb-3">
            <a href="openuniv_major.php?m=<?php echo $m['id']; ?>" class="text-decoration-none">
                <div class="card book-card h-100"><div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($m['name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($m['desc']); ?> • <?php echo count($subs); ?> مواد</p>
                </div></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
