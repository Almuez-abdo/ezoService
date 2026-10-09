<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
$pageTitle = "المكتبة العامة - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
include "includes/app_data.php";
$cat = $_GET['cat'] ?? '';
?>
<div class="container mt-4">
    <h3 class="ez-sec-title">المكتبة العامة</h3>
    <p class="text-muted">كتب إنجليزية وثقافية ودينية وكل ما تعنيه الكلمة من مكتبة</p>
    <div class="ez-pills">
        <a href="library.php" class="<?php echo $cat===''?'on':''; ?>">الكل</a>
        <?php foreach($APP_GENERAL_CATS as $c): ?>
            <a href="library.php?cat=<?php echo $c['id']; ?>" class="<?php echo $cat===$c['id']?'on':''; ?>"><?php echo htmlspecialchars($c['name']); ?></a>
        <?php endforeach; ?>
    </div>
    <?php foreach($APP_GENERAL_CATS as $c):
        if($cat!=='' && $cat!==$c['id']) continue;
        $books = array_values(array_filter($APP_GENERAL_BOOKS, fn($b)=>$b['cat']===$c['id']));
    ?>
        <h4 class="mt-4 mb-3"><?php echo htmlspecialchars($c['name']); ?> • <?php echo count($books); ?></h4>
        <div class="row">
            <?php foreach($books as $b): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card book-card h-100">
                    <img src="<?php echo coverImg($b['id']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($b['title']); ?>" loading="lazy">
                    <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($b['title']); ?></h5>
                    <p class="card-text text-muted"><small><?php echo htmlspecialchars($b['author']); ?> • <?php echo $b['pages']; ?> صفحة</small></p>
                    <p class="card-text"><?php echo htmlspecialchars($b['desc']); ?></p>
                    <span class="badge bg-danger align-self-start">PDF</span>
                </div></div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php include "includes/footer.php"; ob_end_flush(); ?>
