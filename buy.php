<?php
    ob_start();

    session_start();
    $pageTitle  = "متابعة الشكوي" ;
    include "includes/func/function.php";
    include "includes/header.php";
    include "includes/nav.php";
    include "conect.php";



    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name       = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
        $code       = filter_var($_POST['code'], FILTER_SANITIZE_NUMBER_INT);

        
            $stFollow = $con->prepare("SELECT
                                             * 
                                        FROM 
                                            information 
                                        INNER JOIN 
                                            sections
                                        ON
                                            sections.ID = information.Section_Id
                                        WHERE ID_Complaints = ? AND P_Name = ?");
            $stFollow->execute(array($code, $name));
            $count = $stFollow->rowCount();
            $follow = $stFollow->fetch();

        $maxAttempt = 3;
        $lockoutTime = 3600;
        
        if(!(isset($_SESSION['attempt']))){
                $_SESSION['attempt'] = 0;
        }
        
        if(isset($_SESSION['lockouts']) && time() < $_SESSION['lockouts']){
            $remaining = $_SESSION['lockouts'] - time();
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60 ;
                    
        }else if($_SESSION['attempt'] < $maxAttempt & $count > 0){
                    $_SESSION['userName']= $name;
                    $_SESSION['ID'] = $code;
                    header('location: client.php');
                    exit();

                    $_SESSION['attempt'] = 0;
            
        }else{
            $_SESSION['attempt']++;
            if($_SESSION['attempt'] >= $maxAttempt){
                $_SESSION['lockouts'] = time() + $lockoutTime;
                $theMsg= '<div class= "alert alert-danger"> لقد تجاوزت العدد المحدد لتسجيل الدخول يجب المحاولة بعد ساعة </div>';

                echo $theMsg;
            }else{
                $remaining = $maxAttempt - $_SESSION['attempt'];
                $theMsg= '<div class= "alert alert-danger"> اسم المستخدم او رقم الشكوي خاطئة تبقت لك ' . $remaining . 'محاولة" </div>';

                echo $theMsg;
            }
        }
    }
     
?>
    <div class="container">
        <h3 class="pt-4">متابعة الشكوي</h3>
        <form class= "oldComplaints" action= " <?php echo $_SERVER['PHP_SELF'] ?>" method = "POST">
             <div class= "input_cont">
                <input class="form-control" type="text" name= "user" placeholder= "الاسم رباعي" autocomplete="off" required/>
                <span class= "asterisk">*</span>
            </div>
            <div class= "input_cont">
                <input class="form-control" type="text" name= "code" pattern="^\d+$" placeholder= "رقم الشكوي" autocomplete="off" />
                <span class= "asterisk">*</span>
            </div>
            <button type="submit" class="btn btn-primary my-3">متابعة</button>
        </form>
    </div>
    
            

    <?php

        include "includes/footer.php";
        
        ob_end_flush();

    ?>