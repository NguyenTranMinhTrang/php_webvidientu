<?php
    session_start();
    require_once dirname( __DIR__ ) . '/utils/utility.php';
    require_once dirname( __DIR__ ) . '/db/dbhelper.php';


    function changepassword($email, $newPass1, $newPass2) {
        $error = "";

        if (empty($newPass1)) {
            $error = "Missing Input";
        }
        else if (empty($newPass2)) {
            $error = "Missing Input";
        }
        else if ($newPass1 != $newPass2) {
            $error = "Confirm password must be the same";
        }
        else {
            $hass = md5Security($newPass1);
            $sql = "UPDATE users SET password = '$hass' WHERE email = '$email'";
            execute($sql);
        }

        if (!empty($error)) {
            $res = [
                'code' => 1,
                'msg' => "Success"
            ];
        }
        else {
            $res = [
                'code' => 1,
                'error' => $error
            ];
        }

        return $res;
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
                    $state = $user['idState'];
                    $token = md5Security($usernameUser.time().$idUser);
                    setcookie('token', $token, time() + 7*24*60*60, "/");
                    $_SESSION['id'] = $idUser;
                    $_SESSION['username'] = $usernameUser;
                    $_SESSION['email'] = $user['email'];

                    $sql = "SELECT * FROM times_login WHERE id = '$idUser'";
                    $data = executeResult($sql, true);
                    if ($data != null) {
                        if ($data['times'] >= 3 ) {
                            $oldState = $data['oldState'];
                            $sql = "UPDATE users SET idState = '$oldState'";
                            execute($sql);
                        }
                        $sql = "DELETE FROM times_login WHERE id = '$idUser'";
                        execute($sql);
                    }

                    if ($usernameUser == "admin") {
                        $code = 1;
                        $_SESSION['state'] = "05";
                        $_SESSION['chucvu'] = "admin";
                    }
                    else {
                        $code = 1;
                        $_SESSION['state'] = $state;
                        $_SESSION['chucvu'] = "user";
                    }
                    $sql = "insert into login_tokens (id_user, token) values ('$idUser', '$token')";
                    execute($sql);
            }
            else {
                $code = 2;
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

    function loginWrong($username) {
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $user = executeResult($sql, true);
        if ($user) {
            $id = $user['id'];
            $sql = "SELECT * FROM times_login WHERE id = '$id'";
            $data = executeResult($sql, true);
            if ($data == null) {
                $sql = "INSERT INTO times_login(id, times)  VALUES('$id', 1)";
                execute($sql);
                return 1;
            }
            else {
                if ($data['times'] == 2) {
                    $oldState = $user['idState'];
                    $sql = "UPDATE users SET idState = '07' WHERE id = '$id'";
                    execute($sql);
                    setcookie('login', true, time() + 60, "/");
                    $timeLogin = "UPDATE times_login SET times = times + 1, oldState = '$oldState' WHERE id = '$id'";
                }
                else if ($data['times'] == 5) {
                    $timelock = date('Y-m-d H:i:s');
                    $sql = "UPDATE users SET idState = '03' WHERE id = '$id'";
                    execute($sql);
                    $timeLogin = "UPDATE times_login SET times = times + 1, datelock = '$timelock' WHERE id = '$id'";
                }
                else {
                    $timeLogin = "UPDATE times_login SET times = times + 1 WHERE id = '$id'";
                }
                execute($timeLogin);
            }

            return $data['times'] + 1;
        }
    }


?>