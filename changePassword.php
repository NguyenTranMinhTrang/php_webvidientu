<?php
    require_once ('./api/authen.php');

    $error = "";
    if (isset($_POST['password1']) && isset($_POST['password2'])) {
        $newPass1 = $_POST['password1'];
        $newPass2 = $_POST['password2'];
        $email = $_POST['email'];
        
        $data = changepassword($email, $newPass1, $newPass2);
        if ($data) {
            if ($data['code'] == 0) {
                $error = $data['error'];
            }
            else {
                unset($_SESSION['forgetPass']);
                header('Location: index.php');
                exit();
            }
        }
    }

    include('header.php');
?>

<body>
    <div class="container">
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Nhập Mật Khẩu Mới Hai Lần</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">

                    <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                    <form id="changePassword" class="form-horizontal" action="" method="post">
                        <input type="hidden" class="form-control" name="email" value="<?= $_SESSION['forgetPass']?>"
                                placeholder="OTP">
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="password1" value=""
                                placeholder="Password">
                        </div>

                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="password2" value=""
                                placeholder="Password">
                        </div>

                        <div style="margin-bottom: 25px" class="input-group">
                            <?php
                                if (!empty($error)) {
                                    echo "<div class='alert alert-danger'>$error</div>";
                                }
                            ?>
                        </div>

                        <div style="margin-top:10px" class="form-group">
                            <!-- Button -->

                            <div class="col-sm-12 controls">
                                <button id="btn-signup" type="submit" class="btn btn-info"><i
                                        class="icon-hand-right"></i>
                                    Send</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="./main.js"></script>
</body>