<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include "includes/func/function.php";
include "includes/app_data.php";
$id = $_GET['subject'] ?? 'arabic';
$name = appSubjectName($id);
$pageTitle = "اختبار $name - Ezzo Service";
$current_page = basename($_SERVER['PHP_SELF']);
include "includes/header.php";
include "includes/nav.php";
include "includes/conect.php";
$questions = quizOf($id);
?>
<div class="container mt-4" style="max-width:760px">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="sudani.php">الشهادة السودانية</a></li>
        <li class="breadcrumb-item active">اختبار <?php echo htmlspecialchars($name); ?></li>
    </ol></nav>
    <h3 class="ez-sec-title">اختبار <?php echo htmlspecialchars($name); ?> (<?php echo count($questions); ?> أسئلة)</h3>
    <form id="quizForm">
    <?php foreach($questions as $i=>$q): ?>
        <div class="card mb-3"><div class="card-body" data-answer="<?php echo $q['a']; ?>">
            <h5><?php echo ($i+1).'. '.htmlspecialchars($q['q']); ?></h5>
            <?php foreach($q['o'] as $j=>$opt): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="q<?php echo $i; ?>" value="<?php echo $j; ?>" id="q<?php echo $i.'_'.$j; ?>">
                    <label class="form-check-label" for="q<?php echo $i.'_'.$j; ?>"><?php echo htmlspecialchars($opt); ?></label>
                </div>
            <?php endforeach; ?>
            <div class="alert alert-info mt-2 d-none explain">شرح: <?php echo htmlspecialchars($q['e']); ?></div>
        </div></div>
    <?php endforeach; ?>
        <button type="button" class="btn-ez w-100 mb-4" onclick="gradeQuiz()">تسليم ورؤية النتيجة</button>
    </form>
    <div id="quizResult" class="alert alert-success d-none text-center fs-4 fw-bold"></div>
</div>
<script>
function gradeQuiz(){
    let score=0, total=<?php echo count($questions); ?>;
    document.querySelectorAll('#quizForm .card-body').forEach(function(card, i){
        const ans=parseInt(card.dataset.answer,10);
        const sel=card.querySelector('input[type=radio]:checked');
        card.querySelectorAll('.form-check').forEach(function(row,j){
            row.classList.remove('text-success','text-danger','fw-bold');
            if(j===ans) row.classList.add('text-success','fw-bold');
            if(sel && parseInt(sel.value,10)===j && j!==ans) row.classList.add('text-danger');
        });
        if(sel && parseInt(sel.value,10)===ans) score++;
        card.querySelector('.explain').classList.remove('d-none');
    });
    const r=document.getElementById('quizResult');
    r.classList.remove('d-none');
    r.textContent='نتيجتك: '+score+' / '+total+(score===total?' — ممتاز! استمر':(score>=total/2?' — جيد جداً':' — راجع الدروس وحاول مجددا'));
    window.scrollTo({top:r.offsetTop-80,behavior:'smooth'});
}
</script>
<?php include "includes/footer.php"; ob_end_flush(); ?>
