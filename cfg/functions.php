<?php
    require_once('db.php');
    require_once ('MFA_Functions.php');

    $mfaFunctions = new MFAFunctions();


    class userHandler{
        protected $dbHandler;
        protected $mailHandler;

        public function setDb($dbHandler){
            $this->dbHandler = $dbHandler;
        }
        public function setMail($mailHandler){
            $this->mailHandler = $mailHandler;
        }


        function checkEmail($email){
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return "Invalid Email";


            $stmt = $this->dbHandler->prepX('SELECT * FROM `users` WHERE `email` = :email', [':email' => $email]);
            if($stmt->rowCount() > 0)
                return "Email already taken!";

            return true;
        }

        function checkUsername($username){
            if(strlen(trim($username)) < 4)
                return "Username must be at least 4 characters!";
            elseif(strlen(trim($username)) > 32)
                return "Username cannot be longer than 32 characters!";
            elseif(!ctype_alnum(trim($username)))
                return "Username can only contain letters and numbers!";

            $stmt = $this->dbHandler->prepX('SELECT * FROM `users` WHERE `username` = :username', [':username' => $username]);
            if($stmt->rowCount() > 0)
                return "Username already taken!";


            return true;

        }

        function checkPassword($password, $confirmPassword){
            if(strlen(trim($_POST['password'])) < 6)
                return "Password must be at least 6 characters!";

            if(trim($password) != trim($confirmPassword))
                return "Passwords do not match!";



            return true;
        }

        function insertUser($username, $email, $password){
            $passwordHash = password_hash(trim($password), PASSWORD_DEFAULT);
            $randomString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);


            if(!$this->dbHandler->prepX("INSERT INTO `users` (username, email, password, resetKey) VALUES (:username, :email, :password, :resetKey)", [':username' => $username, ':email' => $email, ':password' => $passwordHash, ':resetKey' => $randomString])){
                return false;
            }
            $userID = $this->dbHandler->lastInsertId('id');

            $emailSubject = "EXAMPLE | Confirm your email.";

            $emailContent = "
                                <p>Thanks for taking the interest to register at EXAMPLE</p>
                                <p>To finish your account registration, please follow this link: <a href=\"".WEBURL."activate.php?id=".$userID."&type=1&code=".$randomString."\">click me</a></p>
                                <p>- Website Administration at EXAMPLE</p>;
                            ";
            
            $this->sendEmail($email, $emailSubject, $emailContent);

            return true;
        }

        function isLoggedIn(){
            if(isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true)
                return true;
            else
                return false;
        }

        function is2FAPassed(){
            if($this->get2FAStatus($_SESSION['userID']) == false)
                return true;
            else{
                if(isset($_SESSION['2faPassed']) && $_SESSION['2faPassed'] === true)
                    return true;
                else
                    return false;
            }
        }

        function doLogin($username, $password){
            $stmt = $this->dbHandler->prepX('SELECT * FROM `users` WHERE `username` = :username', [':username' => $username]);
            if($stmt->rowCount() !== 1){
                return "Username not found.";
            }else{
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $storedPassword = $row['password'];
                    $storedID       = $row['id'];
                    $status         = $row['status'];
                }

                if(!password_verify($password, $storedPassword)){
                    return "Incorrect password!";
                }else{
                    if($status == 0){
                        return "Account not yet activated!";
                    }elseif($status == 1){
                        $_SESSION["loggedIn"] = true;
                        $_SESSION["username"] = $username;
                        $_SESSION["userID"] = $storedID;
                    }elseif($status == 2){
                        return "Account suspended.";
                    }


                    return true;
                }
            }
        }

        function getEmail($userID){
            $stmt = $this->dbHandler->prepX('SELECT `email` FROM `users` WHERE `id` = :id', [':id' => $userID]);
            if($stmt->rowCount() !== 1){
                return false;
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['email'];
            }
        }

        function getUsername($userID){
            $stmt = $this->dbHandler->prepX('SELECT `username` FROM `users` WHERE `id` = :id', [':id' => $userID]);
            if($stmt->rowCount() !== 1){
                return false;
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['username'];
            }
        }
        function get2FAStatus($userID){
            $stmt = $this->dbHandler->prepX('SELECT `otp_active` FROM `users` WHERE `id` = :id', [':id' => $userID]);
            if($stmt->rowCount() !== 1){
                return false;
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if($row['otp_active'] == 0)
                    return false;
                else
                    return true;
            }
        }
        function toggle2FAStatus($userID){
            if($this->get2FAStatus($userID) == false){
                $this->dbHandler->prepX('UPDATE `users` SET `otp_active` = 1 WHERE `id` = :id', [':id' => $userID]);
            }else{
                $this->dbHandler->prepX('UPDATE `users` SET `otp_active` = 0 WHERE `id` = :id', [':id' => $userID]);
            }
            return true;
        }

        function changePassword($userID, $password, $confirmPassword){
            $result = $this->checkPassword($password, $confirmPassword);
            if($result !== true)
                return $result;

            $passwordHash = password_hash(trim($password), PASSWORD_DEFAULT);

            $this->dbHandler->prepX('UPDATE `users` SET `password` = :passhash WHERE `id` = :id', [':id' => $userID, ':passhash' => $passwordHash]);

            return true;
        }

        function changeEmail($userID, $email){
            $result = $this->checkEmail($email);
            if($result !== true)
                return $result;

            $randomString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);

            $this->dbHandler->prepX('UPDATE `users` SET `new_email` = :email, `resetKey` = :resetKey WHERE `id` = :id', [':id' => $userID, ':email' => $email, ':resetKey' => $randomString]);

            $emailSubject = "EXAMPLE | Confirm your email.";

            $emailContent = "<p>An email change has been requested for your account.</p>
                                <p>If you did not request this reset, please change your password.</p>
                                <p>To finish your email change, please follow this link: <a href=\"".WEBURL."activate.php?id=".$userID."&type=3&code=".$randomString."\">click me</a></p>
                                <p>- Website Administration at EXAMPLE</p>;
                            ";
            
            $this->sendEmail($email, $emailSubject, $emailContent);

            return true;
        }

        function change2FASecret($userID, $secret){
            $this->dbHandler->prepX('UPDATE `users` SET `otp_secret` = :secretKey WHERE `id` = :id', [':id' => $userID, ':secretKey' => $secret]);
            return true;
        }

        function get2FASecret($userID){
            $stmt = $this->dbHandler->prepX('SELECT `otp_secret` FROM `users` WHERE `id` = :id', [':id' => $userID]);
            if($stmt->rowCount() !== 1){
                return false;
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['otp_secret'];
            }
        }


        function passwordReset($email){
            $stmt = $this->dbHandler->prepX('SELECT `id` FROM `users` WHERE `email` = :email', [':email' => $email]);
            if($stmt->rowCount() !== 1){
                return false;
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $userID = $row['id'];
            }


            $randomString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);

            $this->dbHandler->prepX('UPDATE `users` SET `resetKey` = :resetKey WHERE `email` = :email', [':email' => $email, ':resetKey' => $randomString]);

            $emailSubject = "EXAMPLE | Password Reset Request.";

            $emailContent = "<p>A password reset has been requested for your account.</p>
                                <p>If you did not request this reset, please change your password.</p>
                                <p>To finish your password change, please follow this link: <a href=\"".WEBURL."activate.php?id=".$userID."&type=2&code=".$randomString."\">click me</a></p>
                                <p>- Website Administration at EXAMPLE</p>;
                            ";
            
            $this->sendEmail($email, $emailSubject, $emailContent);

            return true;
        }


        function sendEmail($email, $title, $content){
            $this->mailHandler->isSMTP();                                                       //Send using SMTP
            $this->mailHandler->Host       = SMTP_HOST;                                         //Set the SMTP server to send through
            $this->mailHandler->SMTPAuth   = true;                                              //Enable SMTP authentication
            $this->mailHandler->Username   = SMTP_USER;                                         //SMTP username
            $this->mailHandler->Password   = SMTP_PASSWORD;                                     //SMTP password
            $this->mailHandler->SMTPSecure = $this->mailHandler::ENCRYPTION_SMTPS;              //Enable implicit TLS encryption
            $this->mailHandler->Port       = SMTP_PORT;   
                                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $this->mailHandler->setFrom(SITEEMAIL, 'xNala\'s PHP-LoginRegister');
            $this->mailHandler->addAddress($email);
            $this->mailHandler->isHTML(true);
            
            $this->mailHandler->Subject = $title;
            $this->mailHandler->Body = $content;

            $this->mailHandler->Send();
        }
    }

    $userHandler = new userHandler();
    $userHandler->setDb($dbHandler);
    $userHandler->setMail($mailHandler);


?>