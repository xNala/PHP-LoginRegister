<?php

    require_once('cfg/functions.php');

    if(!$userHandler->isLoggedIn()){
        header('location: login.php');
        exit();
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
                <p>Welcome back, <?php echo $_SESSION['username']; ?></p>
                <p>User email: <?php echo $userHandler->getEmail($_SESSION['userID']); ?></p>
                <p>2FA Status: disabled</p>
                <div class="control-group">
                    <a href="logout.php" class="btn btn-primary btn-logout">logout!</a>
                </div>
            </div>
        </div>
    </body>
</html>