<?php

    //title function

    function getTitle(){
        global $pageTitle;
        if(isset($pageTitle)){
            echo $pageTitle;
        }else{
            echo 'No Title';
        }
    };

    // Redirect To Home
    
    function redirectHome($theMsg, $sec = 3, $url= NULL){
        if($url === NULL){
            $url = 'index.php';
            $link = 'الصفحة الرئيسية';
        }elseif($url === 'back'){
            if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== ''){
                $url = $_SERVER['HTTP_REFERER'];
                $link = 'الصفحة السابقة';
            }
        }elseif($url == $url){
            $url = $url;
            $link = 'الصفحة الرئيسية '; 
        }
     
    

        echo '<div class= "container text-end">';
            echo $theMsg ;
            echo '<div class= "alert alert-info">سوف يتم تحويلك الي  ' .  $link . ' بعد ' . $sec . ' ثانية </div>';
        echo '</div>';
        header("refresh:$sec;URL= $url");
        exit();
    }
// function.php - دوال مساعدة للمشروع

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function displayPrice($price) {
    return number_format($price, 2) . ' ج.س';
}

function getBookConditionBadge($condition) {
    $badges = [
        'جديد' => 'success',
        'مثل الجديد' => 'info',
        'جيد' => 'warning',
        'مقبول' => 'secondary'
    ];
    
    $color = isset($badges[$condition]) ? $badges[$condition] : 'secondary';
    return "<span class='badge bg-$color'>$condition</span>";
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
}

function generateTransactionId() {
    return 'TXN_' . time() . '_' . rand(1000, 9999);
}

function validateCardNumber($number) {
    $number = preg_replace('/\s+/', '', $number);
    return preg_match('/^[0-9]{16}$/', $number);
}

function validateExpiryDate($date) {
    return preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $date);
}

function validateCVV($cvv) {
    return preg_match('/^[0-9]{3,4}$/', $cvv);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>