<?php
    ob_start();

    session_start();
    $pageTitle = "Login";

    if (isset($_SESSION['UserName'])){
        header('location: homePage.php');
        exit();
    }
    include "init.php";
    


    if($_SERVER ['REQUEST_METHOD'] == "POST"){
        
        $user = filter_var ($_POST['user'], FILTER_SANITIZE_STRING);
        $password = $_POST['pass'];
        $hashpass = sha1($password);
        

            $stmt = $con-> prepare("SELECT 
                                        *
                                    FROM
                                        admin
                                    WHERE
                                        userName = ?
                                    AND
                                        Password = ? 
                                    ");
            $stmt-> execute(array($user, $hashpass));
            $stLog = $stmt->fetch();
            $count = $stmt-> rowCount();

        $maxAttempts = 3;
        $lockoutTime = 3600;
        if(!isset($_SESSION['attempts'])){
            $_SESSION['attempts'] = 0;
        }
        if(isset($_SESSION['lockout']) && time() < $_SESSION['lockout']){
            $remaining = $_SESSION['lockout'] - time();
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60 ;
        
        }else if($_SESSION['attempts'] < $maxAttempts & $count > 0){
                    $_SESSION['userName']= $user;
                    $_SESSION['ID'] = $stLog['ID'];
                    header('location: homePage.php');
                    exit();

                    $_SESSION['attempts'] = 0;
        }else{
            $_SESSION['attempts']++;
            if($_SESSION['attempts'] >= $maxAttempts){
                $_SESSION['lockout'] = time() + $lockoutTime;
                $theMsg= '<div class= "alert alert-danger"> لقد تجاوزت العدد المحدد لتسجيل الدخول يجب المحاولة بعد ساعة </div>';

                echo $theMsg;
            }else{
                $remaining = $maxAttempts - $_SESSION['attempts'];
                $theMsg= '<div class= "alert alert-danger"> اسم المستخدم او كلمة المرور خاطئة تبقت لك ' . $remaining . 'محاولة" </div>';

                echo $theMsg; 
            }
    }
}
    
?>



    <div class="container admin_log">
            <div class= "col-lg-offsit-5 main">
                <h3>تسجيل الدخول</h3>
                <form class= "login" action= "<?php echo $_SERVER['PHP_SELF'] ?>" method = "POST">
                    <div class= "input_cont">
                        <input class="form-control" type="text" name= "user" placeholder= "الاسم" autocomplete="off" />
                        <span class= "asterisk">*</span>
                    </div>
                    <div class= "input_cont">
                        <input class="form-control" type="password" name= "pass" placeholder="كلمة السر" autocomplete= "new-password" />
                        <span class= "asterisk">*</span>
                    </div>
                        <input class= "btn btn-primary my-3" type= "submit" name= "login_attemps" value= "دخول" />  
                </form>
            </div>
        </div>
    </div>
            
<?php
    include $temp . "footer.php";

    ob_end_flush();
?>