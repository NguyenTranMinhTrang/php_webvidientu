<?php
    require_once ('./api/authen.php');

    if (isset($_SESSION['id'])) {
        header('Location: index.php');
        exit();
    }

    $error = '';

    if (isset($_COOKIE['login'])) {
        $error = "Your account have been lock now. Try again after 1 minutes";
    }
    else {
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
    
            $data = login($username, $password);
            if ($data['code'] != 1) {
                $error = $data['error'];
                if ($data['code'] == 2) {
                    if ($username != "admin") {
                        $times = loginwrong($username);
                        if ($times == 6) {
                            $error = "Your account have been lock";
                        }
                    }
                }
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
                    <div class="panel-title">Sign In</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">

                    <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>

                    <form id="loginform" class="form-horizontal" action="" method="post" >
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value=""
                                placeholder="username or email">
                        </div>

                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password"
                                placeholder="password">
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
                                    Login</button>

                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-md-12 control">
                                <div style="border-top: 1px solid #888; padding-top:15px; font-size:85%">
                                    Don't have an account!
                                    <a href="./register.php">
                                        Sign Up Here
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="./main.js"></script>
</body>
