<?php

    define("DB_HOST", "localhost");
    define("DB_NAME", "");
    define("DB_USER", "");
    define("DB_PASSWORD", "");

    define('SITEEMAIL','youremail@riseup.net');
    define('WEBURL', 'http://bipf.hijackedyour.su/LoginRegister/');

    define('SMTP_USER', '');
    define('SMTP_PASSWORD', '');
    define('SMTP_HOST', 'mail.riseup.net');
    define('SMTP_PORT', 465);
    
    //PHPMailer Library
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    $mailHandler = new PHPMailer(false);

    class dbHandler extends PDO
    {
        protected $dbHandler;

        public function setDb($dbHandler){
            $this->dbHandler = $dbHandler;
        }


        public function __construct()
        {
            try {
                $dns = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
            } catch (PDOException $e) {
                print "Error!: " . $e->getMessage() . "<br/>";
                die();
            }
            parent::__construct($dns, DB_USER, DB_PASSWORD);
        }


        public function prepX($sql, $args = [])
        {
            $stmt = $this->dbHandler->prepare($sql);
            $stmt->execute($args);

            return $stmt;
        }

    }

    $dbHandler = new dbHandler();
    $dbHandler->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $dbHandler->setDb($dbHandler);


    if (version_compare(phpversion(), '5.4.0', '<')) {
        if(session_id() == '') {
           session_start();
        } 
    }else{
       if (session_status() == PHP_SESSION_NONE) {
           session_start();
       } 
    }

?>