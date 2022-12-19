<?php

    require_once('cfg/functions.php');

    if($userHandler->isLoggedIn()){
        header('location: index.php');
        exit();
    }

    $err = "";
    $msg = "";

    //die(var_dump(strlen($_GET['resetKey'])));

    if( isset($_GET['resetKey']) && 
        !empty($_GET['resetKey']) && 
        strlen(trim($_GET['resetKey'])) == 32 &&
        isset($_GET['id']) && 
        !empty($_GET['id']) && 
        is_numeric($_GET['id']))
    {
        $stmt = $dbHandler->prepX('SELECT * FROM `users` WHERE `resetKey` = :resetKey AND `id` = :id', ['id' => $_GET['id'], 'resetKey' => $_GET['resetKey']]);
        if($stmt->rowCount() != 1){
            header('location: login.php');
            exit();
        }
    }else{
        header('location: login.php');
        exit();
    }

    if(isset($_POST['reset'])){
        if(!isset($_POST['password']) || empty(trim($_POST['password'])))
            $err = "Please set a Password.";

        if(!isset($_POST['passwordConfirm']) || empty(trim($_POST['passwordConfirm'])))
            $err = "Please confirm your Password.";
        
        if($err != ""){

        }else{

            $result = $userHandler->changePassword($_GET['id'], $_POST['password'], $_POST['passwordConfirm']);

            if($result !== true)
                $err = $result;
            else{
                $dbHandler->prepX('UPDATE `users` SET `resetKey` = NULL WHERE `id` = :id', [':id' => $_GET['id']]);
                $msg = "Password changed!";
                header('location: index.php');
                exit();                
            }
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container">
            <div class="login-card">
                <div class="login-header">
                    <h1>Password Reset</h1>
                </div>

                <div class="login-body">
                    <?php
                        if($err !== "")
                            echo "<p class=\"login-error\">Error: ".$err."</p>";

                        if($msg !== "")
                            echo "<p class=\"login-message\">Notice: ".$msg."</p>";
                    ?>


                    <form method="POST">

                        <div class="control-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" name="password" id="password" type="password" placeholder="Password">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="passwordConfirm">Password</label>
                            <input class="form-input" name="passwordConfirm" id="passwordConfirm" type="password" placeholder="Confirm Password">
                        </div>
                        <input type="hidden" name="restCode" value="<?php echo $_GET['resetKey'] ?>"></input>
                        
                        <div class="control-group">
                            <button type="submit" name="reset" class="btn btn-primary btn-login">Change Password!</button>
                        </div>                      
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>