<?php

    require_once('cfg/functions.php');

    if(!$userHandler->isLoggedIn()){
        header('location: login.php');
        exit();
    }
    if(!$userHandler->is2FAPassed()){
        header('location: 2fa.php');
        exit();
    }

    $pageType = 0;
    $err = "";
    $msg = "";

    if(isset($_GET['msg']) && !empty($_GET['msg']) && $_GET['msg'] == "email")
        $msg = "Email changed!";

    if(isset($_POST['startChangePassword'])){
        $pageType = 1;
    }
    if(isset($_POST['finishChangePassword'])){
        if(!isset($_POST['password']) || empty(trim($_POST['password'])))
            $err = "Please set a Password.";

        if(!isset($_POST['passwordConfirm']) || empty(trim($_POST['passwordConfirm'])))
            $err = "Please confirm your Password.";


        if($err == ""){
            $result = $userHandler->changePassword($_SESSION['userID'], $_POST['password'], $_POST['passwordConfirm']);

            if($result !== true)
                $err = $result;
            else
                $msg = "Password changed!";
        }
    }

    if(isset($_POST['startChangeEmail'])){
        $pageType = 2;
    }
    if(isset($_POST['finishChangeEmail'])){
        if(!isset($_POST['email']) || empty(trim($_POST['email'])))
            $err = "Please set an Email.";

        $result = $userHandler->changeEmail($_SESSION['userID'], $_POST['email']);

        if($result !== true)
            $err = $result;
        else
            $msg = "Email change started! Please verify your email to complete!";
    }
    if(isset($_POST['startChange2FA'])){
        $pageType = 3;

        if($userHandler->get2FAStatus($_SESSION['userID']) == false){
            $secret = $mfaFunctions->generateSecret(32);
            $qrLink = $mfaFunctions->generateQRCode('PHPLoginRegister', $secret, 100);
            $userHandler->change2FASecret($_SESSION['userID'], $secret);            
        }

    }
    if(isset($_POST['finishEnable2FA'])){
        if(!isset($_POST['2faCode']) || empty(trim($_POST['2faCode'])))
            $err = "Please enter a 2FA Code.";

        if(!is_numeric($_POST['2faCode']))
            $err = "Invalid 2FA Code.";

        if($mfaFunctions->compareCode($userHandler->get2FASecret($_SESSION['userID']), $_POST['2faCode'], 2)){
            $userHandler->toggle2FAStatus($_SESSION['userID']);
            $msg = "2FA Activated!";
        }else
            $err = "Invalid 2FA Code.";
    }
    if(isset($_POST['finishDisable2FA'])){
        if(!isset($_POST['2faCode']) || empty(trim($_POST['2faCode'])))
            $err = "Please enter a 2FA Code.";

        if(!is_numeric($_POST['2faCode']))
            $err = "Invalid 2FA Code.";
        
        if($mfaFunctions->compareCode($userHandler->get2FASecret($_SESSION['userID']), $_POST['2faCode'], 2)){
            $userHandler->toggle2FAStatus($_SESSION['userID']);
            $msg = "2FA Disabled! You may safely delete the entry in your app.";
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
            <div class="index-card">
                <p>Welcome back, <?php /* echo $_SESSION['username']; */ echo $userHandler->getUsername($_SESSION['userID']); ?></p>
                <?php
                    if($err !== "")
                        echo "<p class=\"login-error\">Error: ".$err."</p>";

                    if($msg !== "")
                        echo "<p class=\"login-message\">Notice: ".$msg."</p>";
                ?>
                


                <?php if($pageType == 0) { //Regular Index ?>
                <form method="POST"><button type="submit" name="startChangePassword" class="btn btn-primary mb-1">Change Password</button></form>
                <p>User email: <?php echo $userHandler->getEmail($_SESSION['userID']); ?></p>
                <form method="POST"><button type="submit" name="startChangeEmail" class="btn btn-primary mb-1">Change Email</button></form>
                <?php 
                    if($userHandler->get2FAStatus($_SESSION['userID']) == true){
                        echo '<p>2FA Status: Enabled</p>';
                        echo '<form method="POST"><button name="startChange2FA" class="btn btn-primary">Disable 2FA</button></form>'; 
                    }else{ 
                        echo '<p>2FA Status: Disabled</p>'; 
                        echo '<form method="POST"><button name="startChange2FA" class="btn btn-primary">Activate 2FA</button></form>';
                    } 
                ?>
                <?php } ?>

                <?php if($pageType == 1){ //Password Change ?>
                    <form method="POST">
                        <div class="control-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" name="password" id="password" type="password" placeholder="Password">
                        </div>

                        <div class="control-group">
                            <label class="form-label" for="passwordConfirm">Password</label>
                            <input class="form-input" name="passwordConfirm" id="passwordConfirm" type="password" placeholder="Confirm Password">
                        </div>
                        <button type="submit" name="finishChangePassword" class="btn btn-primary mb-1">Change Password</button>
                    </form>
                <?php } ?>


                <?php if($pageType == 2){ //Email Change ?>
                    <form method="POST">
                        <div class="control-group">
                            <label class="form-label" for="email">New Email</label>
                            <input class="form-input" name="email" id="email" type="email" placeholder="New Email">
                        </div>

                        <button type="submit" name="finishChangeEmail" class="btn btn-primary mb-1">Change Email</button>
                    </form>
                <?php } ?>

                <?php if($pageType == 3){ //2FA Management ?>
                        <?php if($userHandler->get2FAStatus($_SESSION['userID']) == false){ ?>
                            <form method="POST">
                                <div class="control-group">
                                    <p>Scan this QR Code in your app, and enter in a code to verify your 2FA activation.</p>
                                    <br>
                                    <center><img src="<?php echo $qrLink; ?>"></img></center>
                                    <br>
                                    <label class="form-label" for="2faCode">2FA Code</label>
                                    <input class="form-input" name="2faCode" id="2faCode" type="text" placeholder="000000">
                                </div>
                                <button type="submit" name="finishEnable2FA" class="btn btn-primary mb-1">Activate 2FA</button>
                            </form>
                        <?php }else{ ?>
                            <form method="POST">
                                <div class="control-group">
                                    <label class="form-label" for="2faCode">2FA Code</label>
                                    <input class="form-input" name="2faCode" id="2faCode" type="text" placeholder="000000">
                                </div>
                                <button type="submit" name="finishDisable2FA" class="btn btn-primary mb-1">Disable 2FA</button>
                            </form>
                        <?php } ?>
                <?php } ?>

                <div class="control-group">
                    <a href="logout.php" class="btn btn-primary btn-logout">logout!</a>
                </div>
            </div>
        </div>
    </body>
</html>