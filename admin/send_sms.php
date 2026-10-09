<?php
    ob_start();


    session_start();

    include "init.php";

    $pageTitle  = "أرسال الرساله" ;


require_once 'vendor/autoload.php'; // مكتبه twilio

use Twilio\Rest\Client;

//بيانات الدخول من حساب twilio - ضعها في متغيرات البيئة ولا ترفعها لـ GitHub
$account_sid = getenv('TWILIO_SID') ?: 'YOUR_TWILIO_SID';
$auth_token = getenv('TWILIO_TOKEN') ?: 'YOUR_TWILIO_TOKEN';
$twilio_number = getenv('TWILIO_NUMBER') ?: '+249000000000';

$stsend = $con->prepare("SELECT P_PHone FROM information WHERE ID_Complaints = ?");
$stsend->execute(array($_SESSION['ID']));
$send = $stsend->fetch();

//رقم المستلم صاحب الشكوي 
$to_number = $send['P_PHone'];
echo $to_number;
$message = "شكرا لتواصلك معنا تم حل شكواك بنجاح";
//أنشاء كائن twilio
$client = new Client($account_sid, $auth_token);

// ارسال الرساله
try {

        $client->messages->create(
        $to_number,
        [
            "from" => $twilio_number,
            "body" => $message
        ]
    );
    echo "تم أرسال الرسالة بنجاح";
}catch (Exception $e) {
    echo "خطأ" . $e->getMessage();
}

$theMsg= '<div class= "alert alert-success"> تم أرسال الرسالة بنجاح</div>';
redirectHome($theMsg, 5, 'homePage.php');


    include $temp . 'footer.php';

    ob_end_flush();
?>