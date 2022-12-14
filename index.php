<?php

    require_once('cfg/functions.php');

    if($userHandler->isLoggedIn() && $userHandler->is2FAPassed()){
        header('location: index.php');
        exit();
    }

    $err = "";

    if(isset($_POST['login'])){
        if(!isset($_POST['2faCode']) || empty(trim($_POST['2faCode'])))
            $err = "Please enter a 2FA Code.";

        if(!is_numeric($_POST['2faCode']))
            $err = "Invalid 2FA Code.";

        if($mfaFunctions->compareCode($userHandler->get2FASecret($_SESSION['userID']), $_POST['2faCode'], 2)){
            $_SESSION['2faPassed'] = true;
            header('location: index.php');
            exit();
        }else
            $err = "Invalid 2FA Code.";
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
                    <h1>2FA Security</h1>
                </div>

                <div class="login-body">
                    <?php
                        if($err !== "")
                            echo "<p class=\"login-error\">Error: ".$err."</p>";
                    ?>


                    <form method="POST">
                        <div class="control-group">
                            <label class="form-label" for="2faCode">2FA Code</label>
                            <input class="form-input" name="2faCode" id="2faCode" type="text" placeholder="000000">
                        </div>

                        <div class="control-group">
                            <button type="submit" name="login" class="btn btn-primary btn-login">Login!</button>
                        </div>                      
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>