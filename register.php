<?php

    require_once('cfg/functions.php');

    if($userHandler->isLoggedIn()){
        header('location: index.php');
        exit();
    }


    $err = "";

    if(isset($_POST['register'])){
        if(!isset($_POST['email']) || empty(trim($_POST['email']))){
            $err = "Please set an Email.";
        }
        if($result = $userHandler->checkEmail($_POST['email'])){
            if($result !== true)
                $err = $result;
        }

        if(!isset($_POST['username']) || empty(trim($_POST['username']))){
            $err = "Please set a Username.";
        }
        if($result = $userHandler->checkUsername($_POST['username'])){
            if($result !== true)
                $err = $result;
        }

        if(!isset($_POST['password']) || empty(trim($_POST['password']))){
            $err = "Please set a Password.";
        }
        if(!isset($_POST['passwordConfirm']) || empty(trim($_POST['passwordConfirm']))){
            $err = "Please confirm your Password.";
        }
        if($result = $userHandler->checkPassword($_POST['password'], $_POST['passwordConfirm'])){
            if($result !== true)
                $err = $result;
        }

        if($err === ""){
            if($userHandler->insertUser(trim($_POST['username']), trim($_POST['email']), trim($_POST['password']))){
                header('location: login.php?msg=registered');
                exit();
            }else{
                $err = "Unknown Error. Please try again later.";
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
                    <h1>Register</h1>
                </div>

                <div class="login-body">

                    <?php
                        if($err !== "")
                            echo "<p class=\"login-error\">Error: ".$err."</p>"
                    ?>

                    <form method="POST">
                        <div class="control-group">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-input" name="username" id="username" type="text" placeholder="Username">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="email">Email &nbsp;&nbsp;</label>
                            <input class="form-input" name="email" id="email" type="email" placeholder="Email">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" name="password" id="password" type="password" placeholder="Password">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="passwordConfirm">Password</label>
                            <input class="form-input" name="passwordConfirm" id="passwordConfirm" type="password" placeholder="Confirm Password">
                        </div>
                        
                        <div class="control-group">
                            <button type="submit" name="register" class="btn btn-primary btn-login">Register!</button>
                        </div>

                        <div class="control-group">
                            <a class="login-link" href="login.php">Already Registered?</a>
                        </div>                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>