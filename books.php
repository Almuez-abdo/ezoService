<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ob_start();
session_start();

$pageTitle = "الكتب";
include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";

$section_id = isset($_GET['section']) ? (int)$_GET['section'] : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $books = searchBooks($con, $q);
    $pageTitle = 'نتائج البحث: ' . $q;
} elseif ($section_id > 0) {
    $books = getBooksBySection($con, $section_id);
    $stName = $con->prepare("SELECT name FROM sections WHERE id = ?");
    $stName->execute([$section_id]);
    $secRow = $stName->fetch();
    $pageTitle = $secRow ? $secRow['name'] : 'الكتب';
} else {
    $books = getAvailableBooks($con, 24);
    $pageTitle = 'جميع الكتب';
}
?>

<div class="container mt-4">
    <h3 class="ez-sec-title"><?php echo htmlspecialchars($pageTitle); ?> (<?php echo count($books); ?>)</h3>

    <?php if(count($books) > 0): ?>
    <div class="row">
        <?php foreach($books as $book): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card book-card h-100">
                    <img src="<?php echo htmlspecialchars(getBookImage($book)); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text text-muted"><small><?php echo htmlspecialchars($book['author'] ?? 'غير محدد'); ?></small></p>
                        <p class="card-text"><?php echo htmlspecialchars(substr($book['description'] ?? '', 0, 100)); ?></p>
                        <p class="ez-price"><?php echo number_format($book['price'], 2); ?> ج.س</p>
                        <div class="d-grid gap-2 mt-auto">
                            <button onclick="buyBook(<?php echo $book['id']; ?>, <?php echo $book['price']; ?>)" class="btn-ez">شراء</button>
                            <button onclick="requestExchange(<?php echo $book['id']; ?>)" class="btn-ez-outline">استبدال</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="alert alert-info text-center">لا توجد نتائج مطابقة.</div>
    <?php endif; ?>
</div>

<script>
function buyBook(bookId, price) {
    if(confirm('تأكيد شراء الكتاب بقيمة ' + price + ' ج.س؟')) {
        window.location.href = 'checkout.php?book_id=' + bookId;
    }
}

function requestExchange(bookId) {
    window.location.href = 'request_exchange.php?book_id=' + bookId;
}
</script>

<?php include "includes/footer.php"; ?>
