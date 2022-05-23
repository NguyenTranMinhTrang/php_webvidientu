<?php
    session_start();
    require_once('../utils/utility.php');
    require_once('../db/dbhelper.php');

    $action = getPost('action');

    switch ($action) {
        case 'xacminh':
            xacminh();
            break;
        
        default:
            # code...
            break;
    }

    function xacminh() {
        $error = "";
        if (!isset($_POST['id'])) {
            $error = "Tham số truyền không hợp lệ";
        }
        else {
            $id = $_POST['id'];
            $sql = "SELECT * FROM users WHERE id = '$id'";
            $user = executeResult($sql, true);
            if ($user) {
                $sql = "UPDATE users SET idState= '02' WHERE id = '$id'";
                execute($sql);
            }
            else {
                $error = "User not found!";
            }

            if (empty($error)) {
                $res = [
                    'code' => 1,
                    'msg' => "Xác minh thành công"
                ];
            }
            else {
                $res = [
                    'code' => 0,
                    'msg' => $error
                ];
            }
        }

        echo json_encode($res);
    }

    function login($username, $password) {
        $error = "";
        $code = 0;
    
        if (empty($username)) {
            $error = "Please enter your username!";
        }
        else if (empty($password)) {
            $error = "Please enter your password!";
        }
        else if (strlen($password) < 6) {
            $error = "Password must have at least 6 characters!";
        }
        else {
            $hashPass = md5Security($password);

            $sql = "select * from users where username = '$username' and password = '$hashPass'";

            $user = executeResult($sql, true);
            if ($user != null) {
                    $usernameUser =  $user['username'];
                    $idUser =  $user['id'];
                    $token = md5Security($usernameUser.time().$idUser);
                    setcookie('token', $token, time() + 7*24*60*60, "/");
                    $_SESSION['id'] = $idUser;
                    $_SESSION['username'] = $usernameUser;
                    if ($usernameUser == "admin") {
                        $code = 2;
                        $_SESSION['chucvu'] = "admin";
                    }
                    else {
                        $code = 1;
                        $_SESSION['chucvu'] = "user";
                    }
                    $sql = "insert into login_tokens (id_user, token) values ('$idUser', '$token')";
                    execute($sql);
            }
            else {
                $error = "Invalid username/password";
            }
        }

        if (!empty($error)) {
            $res = [
                "code" => $code,
                "error" => $error
            ];
        }
        else {
            $res = [
                "code" => $code,
                "msg" => "Login success!"
            ];    
        }
        
        return $res;
    }

    function register($email , $sdt , $name , $birthday , $address) {
        $res ='';
        $error = '';
        $front = $back = '';
        $timestamp = '';
        $character = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if (empty($email)) {
            $error = 'Please enter your email';
        }
        else if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            $error = 'This is not a valid email address';
        }
        else if (empty($name)) {
            $error = 'Please enter your name';
        }
        else if (empty($sdt)) {
            $error = 'Please enter your phone number';
        }
        else if (empty($birthday)) {
            $error = 'Please enter your birthday';
        }
        else if (empty($address)) {
            $error = 'Please enter your address';
        }
        else {
            $resultImageFront = checkUpload($email, "front");
            if ($resultImageFront['code'] == 0) {
                $error = $resultImageFront['error'];
            }
            else {
                $resultImageBack = checkUpload($email, "back");
                if ($resultImageBack['code'] == 0) {
                    $error = $resultImageBack['error'];
                }
                else {
                    $sql = "select * from users where email = '$email'";
                    $result = executeResult($sql, true);
                    if ($result == null || count($result) == 0) {
                        $username = rand(1000000000,9999999999);
                        $password = substr(str_shuffle($character), 0, 6);
                        $hash = md5Security($password);
                        $timestamp = strtotime($birthday); 
                        $front = $resultImageFront['tmp'];
                        $back = $resultImageBack['tmp'];
                        $createAt = date('Y-m-d H:i:s');
                        $sql = "insert into users(email, name, username, password, phone, birthday, address, front, back, idState, createAT)
                        values ('$email', '$name', '$username', '$hash', '$sdt', '$timestamp', '$address', '$front', '$back', '00', '$createAt')";
                        execute($sql);
                        $sql = "SELECT * FROM users WHERE username = '$username' AND  password = '$hash'";
                        $user = executeResult($sql, true);
                        if ($user) {
                            $_SESSION['id'] = $user['id'];
                            $_SESSION['first'] = true;
                        }
                        if (!sendMail($email, $username, $password )) {
                            $error = "Fall to send email to activate";
                        }
                    }
                    else {
                        $error = 'User is already exist!';
                    }
                }
            } 
        }

        if (!empty($error)) {
            $res = [
                "code" => 0,
                "error" => $error
            ];
        }
        else {
            $res = [
                "code" => 1,
                "msg" => "Register success!"
            ];
        }
           
        return $res;
    }

    function firstLogin($newPass1, $newPass2) {
        $error = '';

        $id = $_SESSION['id'];
            
        if ($newPass1 != $newPass2) {
            $error = "Confirm password incorrect!";
        }
        else {
            $sql = "SELECT * FROM users WHERE id = $id";

            $user = executeResult($sql, true);
            if ($user != null) {
                $hashPass = md5Security($newPass1);
                $sql = "UPDATE users SET password ='$hashPass', idState= '01' WHERE id = '$id'";
                execute($sql);
                $usernameUser =  $user['username'];
                $_SESSION['username'] = $usernameUser;
                $_SESSION['chucvu'] = 'user';
                unset($_SESSION['first']);
                $token = md5Security($usernameUser.time().$id);
                setcookie('token', $token, time() + 7*24*60*60, "/");
                $sql = "insert into login_tokens (id_user, token) values ('$id', '$token')";
                execute($sql);
            }
            else {
                $error= "User does not exist";
            }
        }

        if ($error == "") {
            $res = [
                "code" => 1,
                "msg" => "Activate account success!"
            ];
        }
        else {
            $res = [
                "code" => 0,
                "error" => $error
            ];
        }

        return $res;
    }

    function logout() {
        $res = [];
        $token = getCOOKIE('token');
        if (!empty($token)) {
            $res = [
                "code" => 0, 
                "error" => "Can't not found user!"
            ];
        }

        // xoa token khoi database

        $sql = "delete from login_tokens where token = '$token'";
        execute($sql);

        setcookie('token', '', time() -  7*24*60*60, '/');
        session_destroy();
        $res = [
            "code" => 1, 
            "msg" => "Log out success!"
        ];
        return $res;
        die();
    }

    function authenToken() {
        
        $token = getCOOKIE('token');

        if (empty($token)) {
            $res = [
                "code" => 0,
                "error" => "Can't not found user!"
            ];
        }
        else {
            $sql = "select users.* from users, login_tokens where users.id = login_tokens.id_user and 
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
                
                $res = [
                    "code" => 1,
                    "user" => $user
                ]; 
                
            }
            else {
                $res = [
                    "code" => 0,
                    "error" => "Can't not found user!"
                ];
            }
        }

        echo json_encode($res);
    }


?>