<?php
    session_start();
    require_once('../utils/utility.php');
    require_once('../db/dbhelper.php');

    $action = getPost('action');
    switch ($action) {
        case 'login':
            login();
            break;
        case 'register':
            register();
            break;   
        case 'first_login':
            register();
            break;    
        default:
            login();
            break;
    }

    function login() {
        $username = getPost('username');
        $password = getPost('password');
        $res = [];

        if (empty($username)) {
            $res = [
                "code" => 0,
                "msg"=> "Please enter your username!"
            ];
        }
        else if (empty($password)) {
            $res = [
                "code" => 0,
                "msg"=> "Please enter your password!"
            ];
        }
        else if (strlen($password) < 6) {
            $res = [
                "code" => 0,
                "msg"=> "Password must have at least 6 characters!"
            ];
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
                $sql = "insert into login_token (id_user, token) values ('$idUser', '$token')";
                execute($sql);
                $res = [
                    "code" => 1,
                    "msg"=> "Login success!"
                ];
            }
            else {
                $res = [
                    "code" => 0,
                    "error"=> "Invalid username/password"
                ];
            }
        }

        echo json_encode($res);
    }

    function register() {
        $error = '';
        $email = $sdt = $name = $birthday = $address  = $front = $back = '';
        $timestamp = '';
        $character = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (!empty($_POST)) {
            $email = getPost('email');
            $sdt = getPost('sdt');
            $name = getPost('name');
            $birthday = getPost('birthday');
            $address = getPost('address');

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
                            $sql = "insert into users(email, name, username, password, phone, birthday, address, front, back, idState)
                            values ('$email', '$name', '$username', '$hash', '$sdt', '$timestamp', '$address', '$front', '$back', '01')";
                            execute($sql);
                            $_SESSION['first_login'] = true;
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
                    "msg" => $error
                ];
            }
            else {
                $res = [
                    "code" => 1,
                    "msg" => "Register success!"
                ];
            }
            echo json_encode($res); 
        }
    }

    function firstLogin() {
        $username = getPost('username');
        $newPass1 = getPost('newPass1');
        $newPass2 = getPost('newPass2');

        if ($newPass1 != $newPass2) {
            return array('code' => 0, 'error' => "Confirm password incorrect!");
        }

        $sql = "select * from users where username = '$username'";

        $user = executeResult($sql, true);
        if ($user != null) {
            $id = $user['id'];
            $sql = "UPDATE users SET password ='$newPass1' WHERE id = '$id'";
            execute($sql);
            $_SESSION['first_login'] = false;
            // chuyen sang trang home
            header( 'Location: index.php' ) ;
        }
        else {
            return array('code' => 0, 'error' => "User does not exist");
        }

    }

    function logout() {
        $token = getCOOKIE('token');
        if (!empty($token)) {
            header('Location: login.php');
            die();
        }

        // xoa token khoi database

        $sql = "delete from login_tokens where token = '$token'";
        execute($sql);

        setcookie('token', '', time() -  7*24*60*60, '/');
        header('Location: login.php');
        die();
    }

?>