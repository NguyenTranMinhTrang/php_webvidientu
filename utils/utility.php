<?php

    function fixSqlInjection($str) {
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('\'', '\\\'', $str);
        return $str;
    }

    function authenToken() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        $token = getCOOKIE('token');

        if (empty($token)) {
            return null;
        }

        $sql = "select users.* from users, login_tokens where users.id = login_tokens.id and 
        login_tokens.token ='$token'";

        $result = executeResult($sql);

        if ($result != null && count($result) > 0) {
            $user = $result[0];
            if ($user['username'] == 'admin') {
                $user['access'] = array(
                    
                );
            }
            else {
                $user['access'] = array(
                    "home\.php$"
                );
            }
            $_SESSION['user'] = $user;

            return $result[0];
        }

        return null;
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
        $target_dir = "C:/xampp/htdocs/cuoiki/server/uploads/";
        $target_file = $target_dir . basename($_FILES[$filename]["name"]);
        echo $target_file;
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
            return array("code" => 1, "tmp" => $_FILES[$filename]["tmp_name"] );

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

?>
