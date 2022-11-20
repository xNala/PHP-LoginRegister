<?php

    require_once('cfg/functions.php');

    if($userHandler->isLoggedIn()){
        header('location: index.php');
        exit();
    }

    $err = "";
    $msg = "";

    if(isset($_GET['msg']) && !empty(trim($_GET['msg']))){
        switch($_GET['msg']){
            case 'active':
                $msg = "Thank you for confirming your email. Account is now activated.";
                break;
            case 'registered':
                $msg = "Thank you for registering. Please confirm your email.";
                break;

            default:
                break;
        }


    }


    if(isset($_POST['login'])){
        if(!isset($_POST['username']) || empty(trim($_POST['username']))){
            $err = "Please enter your username.";
        }
        if(!isset($_POST['password']) || empty(trim($_POST['password']))){
            $err = "Please enter your password.";
        }

        $result = $userHandler->doLogin(trim($_POST['username']), trim($_POST['password']));
        if($result === true){
            header('location: index.php');
            exit();
        }else{
            $err = $result;
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
                            <label class="form-label" for="username">Username</label>
                            <input class="form-input" name="username" id="username" type="text" placeholder="Username">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" name="password" id="password" type="password" placeholder="Password">
                        </div>
                        
                        <div class="control-group">
                            <button type="submit" name="login" class="btn btn-primary btn-login">Login!</button>
                        </div>

                        <div class="control-group">
                            <a class="login-link" href="register.php">Not Yet Registered?</a>
                        </div>                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>