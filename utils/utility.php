<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
    
    function fixSqlInjection($str) {
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('\'', '\\\'', $str);
        return $str;
    }

    
    function getPost($key) {
        $value = '';
        if (isset($_POST[$key])) {
            $value = $_POST[$key];

        }
        return fixSqlInjection($value);
    }

    function getGet($key) {
        $value = '';
        if (isset($_GET[$key])) {
            $value = $_GET[$key];

        }
        return fixSqlInjection($value);
    }

    function getCOOKIE($key) {
        $value = '';
        if (isset($_COOKIE[$key])) {
            $value = $_COOKIE[$key];

        }
        return fixSqlInjection($value);
    }

    function md5Security($pass) {
        return md5(md5($pass));
    }

    function checkUpload($email, $filename) {
        if ($_FILES[$filename]["name"] == "" && $_FILES[$filename]["full_path"] == "" && $_FILES[$filename]["tmp_name"] == "") {
            return array("code" => 0, "error" => "You haven't choosen image yet!");
        }
        
        $target_dir = "C:/xampp/htdocs/cuoiki/server/uploads/";
        $nameEmail = str_replace('.', '_', $email);
        $target_dir = $target_dir . $nameEmail;
        mkdir($target_dir);
        $target_file = $target_dir . '/' . basename($_FILES[$filename]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    
        $check = getimagesize($_FILES[$filename]["tmp_name"]);
        if(!$check ) {
            return array("code" => 0, "error" => "File is not an image.");
        } 
         
        if (file_exists($target_file)) {
            return array("code" => 0, "error" => "File already exists.");
        }

        if ($_FILES[$filename]["size"] > 500000) {
            return array("code" => 0, "error" => "File is too large.");
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            return array("code" => 0, "error" => "Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if (move_uploaded_file($_FILES[$filename]["tmp_name"], $target_file)) {
            return array("code" => 1, "tmp" => $nameEmail. '/' . basename($_FILES[$filename]["name"]));

        }
        else {
            return array("code" => 0, "error" => "Sorry, there was an error uploading your file.");
        }
    }

    function uploadAgain($email, $filename, $url) {
        if ($_FILES[$filename]["name"] == "" && $_FILES[$filename]["full_path"] == "" && $_FILES[$filename]["tmp_name"] == "") {
            return array("code" => 0, "error" => "You haven't choosen image yet!");
        }

        $target_dir = "C:/xampp/htdocs/cuoiki/server/uploads/";
        

        $nameEmail = str_replace('.', '_', $email);
        $dir = $target_dir . $nameEmail;
        $target_file = $dir . '/' . basename($_FILES[$filename]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $check = getimagesize($_FILES[$filename]["tmp_name"]);
        if(!$check ) {
            return array("code" => 0, "error" => "File is not an image.");
        }

        if ($_FILES[$filename]["size"] > 500000) {
            return array("code" => 0, "error" => "File is too large.");
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            return array("code" => 0, "error" => "Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        
        $oldFile = $target_dir . $url;
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }

        if (move_uploaded_file($_FILES[$filename]["tmp_name"], $target_file)) {
            return array("code" => 1, "tmp" => $nameEmail. '/' . basename($_FILES[$filename]["name"]));

        }
        else {
            return array("code" => 0, "error" => "Sorry, there was an error uploading your file.");
        }

    }

    function checkAccessPermission($uri = false) {
        $uri = $uri != false ? $uri : $_SERVER['REQUEST_URI'];
        $access = $_SESSION['user']['access'];
        $access = implode("|", $access);
        preg_match('/index.php\.php$|'.$access.'/', $uri, $matches);
        return !empty($matches);
    }

    function sendMail($email, $username, $password) {
        
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;               //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'minhtrang.9096@gmail.com';                     //SMTP username
            $mail->Password   = 'fmtpnsolagvyaguo';                               //SMTP password
            $mail->SMTPSecure = 'tls';       //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('minhtrang.9096@gmail.com', 'Mailer');
            $mail->addAddress($email, 'User');     //Add a recipient
            //$mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
        
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Activate your account';
            $mail->Body    = "Username: '$username', Password: '$password'";
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function sendOTP($email, $OTP) {
        $mail = new PHPMailer(true);
        try {

            $mail->SMTPDebug = 0;             
            $mail->isSMTP();                                           
            $mail->Host       = 'smtp.gmail.com';                   
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'minhtrang.9096@gmail.com';              
            $mail->Password   = 'fmtpnsolagvyaguo';                            
            $mail->SMTPSecure = 'tls';       
            $mail->Port       = 587;                                   
        

            $mail->setFrom('minhtrang.9096@gmail.com', 'Mailer');
            $mail->addAddress($email, 'User');     
        
            $mail->isHTML(true);                           
            $mail->Subject = 'Verify OTP code to transfer money';
            $mail->Body    = "Here is your OTP code: $OTP";
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

?>
