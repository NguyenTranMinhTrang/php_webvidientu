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
            firstLogin();
            break;
        case 'logout':
            logout();
            break;
        case 'home':
            authenToken();
        default:
            break;
    }

    function login() {
        $error = "";
        $code = 0;
        
            $username = getPost('username');
            $password = getPost('password');
    
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
                    if ($user['idState'] == '00') {
                        $_SESSION['first_login'] = true;
                        $_SESSION['username'] = $username;
                        $_SESSION['password'] = $password;
                        $error =  "You haven't activate your account!";
                        $code = 3;
                    }
                    else {
                        $usernameUser =  $user['username'];
                        $idUser =  $user['id'];
                        $token = md5Security($usernameUser.time().$idUser);
                        setcookie('token', $token, time() + 7*24*60*60, "/");
                        $sql = "insert into login_tokens (id_user, token) values ('$idUser', '$token')";
                        execute($sql);
                    }                   
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
            if ($user['username'] == "admin") {
                $res = [
                    "code" => 2,
                    "msg" => "Admin"
                ];
            }
            else {
                $res = [
                    "code" => 1,
                    "msg" => "Login success!"
                ];
            }
        }
        echo json_encode($res);
    }

    function register() {
        $res ='';
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
                            values ('$email', '$name', '$username', '$hash', '$sdt', '$timestamp', '$address', '$front', '$back', '00')";
                            execute($sql);
                            $_SESSION['first_login'] = true;
                            $_SESSION['username'] = $username;
                            $_SESSION['password'] = $password;
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
        $error = '';
        if (!isset($_SESSION['first_login']) ) {
            $error = "It's not the first time login";
        }
        else if (!isset($_SESSION['username']) || !$_SESSION['password']) {
            $error = "It's not the first time login";
        }
        else {
            $username = $_SESSION['username'];
            $password = $_SESSION['password'];
            $hash = md5Security($password);
            $newPass1 = getPost('newPass1');
            $newPass2 = getPost('newPass2');
            if ($newPass1 != $newPass2) {
                $error = "Confirm password incorrect!";
            }
            else {
                $sql = "select * from users where username = '$username' and password = '$hash'";
    
                $user = executeResult($sql, true);
                if ($user != null) {
                    $id = $user['id'];
                    $hashPass = md5Security($newPass1);
                    $sql = "UPDATE users SET password ='$hashPass', idState= '01' WHERE id = '$id'";
                    execute($sql);
                    unset($_SESSION['first_login']);
                    unset($_SESSION['username']);
                    unset($_SESSION['password']);
                    $usernameUser =  $user['username'];
                    $idUser =  $id;
                    $token = md5Security($usernameUser.time().$idUser);
                    setcookie('token', $token, time() + 7*24*60*60, "/");
                    $sql = "insert into login_tokens (id_user, token) values ('$idUser', '$token')";
                    execute($sql);
                }
                else {
                    $error= "User does not exist";
                }
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

        echo json_encode($res);
    }

    function logout() {
        $res = [];
        $token = getCOOKIE('token');
        if (!empty($token)) {
            $res = [
                "code" => 0, 
                "msg" => "Can't not found user!"
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
        echo json_encode($res);
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