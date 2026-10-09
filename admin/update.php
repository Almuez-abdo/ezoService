<?php
    ob_start();

    session_start();    

    include 'init.php';

        $userId = isset($_GET['userID']) && is_numeric($_GET['userID']) ? intval($_GET['userID']) : 0;     
        $check= checkItem("ID_Complaints", "information", $userId);
                    
            if($check > 0){
                $stmt = $con->prepare("UPDATE information SET State = 1 WHERE ID_Complaints = ?");
                $stmt->execute(array($userId));

                $_SESSION['ID'] = $userId;
            }
                
        header('location: send_sms.php');


    include $temp . 'footer.php';

?>


