<?php
    require_once ('../api/authen.php');

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }
    else if (!isset($_SESSION['first'])) {
        header('Location: index.php');
        exit();
    }


    $error = '';

    if (isset($_POST['newpw1']) && isset($_POST['newpw2'])) {
        $newPass1 = $_POST['newpw1'];
        $newPass2 = $_POST['newpw2'];
        
        $data = firstLogin($newPass1, $newPass2);
        if ($data) {
            if ($data['code'] == 0) {
                $error = $data['error'];
            }
            else {
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
                    <div class="panel-title">Change your password</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot
                            password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">

                    <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                    <form id="firstLogin" class="form-horizontal" role="form" action="" method="post">

                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="newpw1" value=""
                                placeholder="Your new password">
                        </div>

                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="newpw2"
                                placeholder="Confirm your password">
                        </div>

                        <div style="margin-bottom: 25px" class="input-group">
                            <?php
                                if (!empty($error)) {
                                    echo "<div class='alert alert-danger'>$error</div>";
                                }
                            ?>
                        </div>


                        <div class="input-group">
                            <div class="checkbox">
                                <label>
                                    <input id="login-remember" type="checkbox" name="remember" value="1"> Remember me
                                </label>
                            </div>
                        </div>


                        <div style="margin-top:10px" class="form-group">
                            <!-- Button -->

                            <div class="col-sm-12 controls">
                                <button id="btn-signup" type="submit" class="btn btn-info"><i
                                        class="icon-hand-right"></i>
                                    Submit</button>

                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-md-12 control">
                                <div style="border-top: 1px solid #888; padding-top:15px; font-size:85%">
                                    Don't have an account!
                                    <a href="./register.html">
                                        Sign Up Here
                                    </a>

                                    <a type="button" href="logout.php" id="a-logout" class="btn btn-primary login-btn btn-block">Đăng xuất</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>