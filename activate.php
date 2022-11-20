<?php

    require_once('cfg/functions.php');

    if(!isset($_GET['id']) || empty(trim($_GET['id'])) || !is_numeric(trim($_GET['id']))){
        die('Invalid Account ID.');
    }else
        $accountID = trim($_GET['id']);

    if(!isset($_GET['type']) || empty(trim($_GET['type'])) || !is_numeric(trim($_GET['type']))){
        die('Invalid Request Type1.');
    }else
        $requestType = trim($_GET['type']);

    if(!isset($_GET['code']) || empty(trim($_GET['code'])) || strlen(trim($_GET['code'])) !== 32){
        die('Invalid Reset Code.');
    }else
        $resetCode = trim($_GET['code']);



    $stmt = $dbHandler->prepX('SELECT * FROM `users` WHERE `id` = :id', ['id' => $accountID]);
    if($stmt->rowCount() != 1){
        die('Invalid Account ID.');
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $storedCode = $row['resetKey'];
    }
    if($storedCode == NULL)
        die('Invalid Request.');

    if($storedCode !== $resetCode){
        die('Invalid Reset Code.');
    }


    if($requestType == 1){
        //Account Activation
        $dbHandler->prepX('UPDATE `users` SET `status` = 1, `resetKey` = NULL WHERE `id` = :id', [':id' => $accountID]);

        header('location: login.php?msg=active');
        exit();

    }elseif($requestType == 2){
        //Password Reset

    }else{
        die('Invalid Request Type.');
    }
?>