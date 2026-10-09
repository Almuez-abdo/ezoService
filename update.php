<?php

ob_start();
session_start();

use const Dom\VALIDATION_ERR;

$pageTitle = "update";

include "includes/func/function.php";
include "includes/header.php";
include "includes/nav.php";
include "conect.php";
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        echo '<div class= "container text-start">';

        $name       = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
        $mail       = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $age        = filter_var($_POST['age'], FILTER_SANITIZE_NUMBER_INT);
        $phone     = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
        $section    = $_POST['sec'];
        $mess       = filter_var($_POST['message'], FILTER_SANITIZE_STRING);


        // Validate The Form
        $formError = array();

        if(strlen($name) < 11 ){
            $formError[]= ' الاسم يجب ان لا يقل عن  <strong> 11 حرف </strong>';
        }
        if(!(preg_match("/^[\p{L}\s]+$/u", $name))){
            $formError[]= ' الاسم الشخصي يجب ان يحتوي علي احرف فقط';
        }
        if($age < 18){
            $formError[]= 'يجب ان يكون عمرك اكبر من <strong> 18 سنه </strong>';
        }
        if(preg_match('/^249[0-9]{9}$/', $phone)){
        }else{
           $formError[]= 'رقم الهاتف غير صحيح';
        }

       
        if(! empty($formError)){
            foreach($formError as $error){
                echo '<div class= "alert alert-danger">' . $error . '</div>';
            }
        }else{
       
            $stmt = $con-> prepare("INSERT INTO 
                                            information(P_Name, P_Email, P_Age, P_PHone, Section_Id, Complaint_Area, Date, State)
                                    VALUES(:Zname, :zmail, :zage, :zphone, :zsec, :zarea, now(), 0);
                                        ");
                $stmt->execute(array(
                    'Zname'     => $name,
                    'zmail'     => $mail,
                    'zage'      => $age,
                    'zphone'    => $phone,
                    'zsec'      => $section,
                    'zarea'     => $mess
                ));

                
                $stcode = $con-> prepare("SELECT * FROM information WHERE P_Name = '$name'");
                $stcode->execute();
                $codes = $stcode->fetchAll();
                foreach($codes as $code);
                $theMsg= '<div class= "alert alert-success"> تمت رفع شكوتك بنجاح وارسال الرمز(' . $code['ID_Complaints'] . ') الي رقم هاتفك  </div>';
                redirectHome($theMsg, 6, 'index.php');
            
         }
    }else {
            $theMsg= '<div class= "alert alert-danger"> لايمكن الدخول الي هذه الصفحة مباشرة </div>';
            redirectHome($theMsg, 6);
    }
    
    echo '</div>';



    include "includes/footer.php";
    
    ob_end_flush();

?>