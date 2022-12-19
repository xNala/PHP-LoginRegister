<?php

    require_once('cfg/functions.php');

    if($userHandler->isLoggedIn()){
        header('location: index.php');
        exit();
    }

    $err = "";
    $msg = "";

    if(isset($_POST['reset'])){
        if(!isset($_POST['email']) || empty(trim($_POST['email']))){
            $err = "Please enter your email.";
        }

        if($userHandler->passwordReset($_POST['email'])){
            $msg = "Password reset requested. Please check your email.";
        }else{
            $err = "No account found with this email.";
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
                    <h1>Login</h1>
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
                            <label class="form-label" for="email">Email</label>
                            <input class="form-input" name="email" id="username" type="email" placeholder="Account Email">
                        </div>
                        
                        <div class="control-group">
                            <button type="submit" name="reset" class="btn btn-primary btn-login">Request Reset!</button>
                        </div>                      
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>