<?php
    require_once ('../api/authen.php');

    if (isset($_SESSION['id'])) {
        header('Location: index.php');
        exit();
    }
    $error = "";
    if (isset($_POST['email']) && isset($_POST['sdt']) && isset($_POST['name']) && isset($_POST['birthday']) && isset($_POST['address'])) {
        $email = getPost('email');
        $sdt = getPost('sdt');
        $name = getPost('name');
        $birthday = getPost('birthday');
        $address = getPost('address');

        $data = register($email, $sdt, $name, $birthday, $address);
        if ($data['code'] == 0) {
            $error = $data['error'];
        }
        else {
            header('Location: firstLogin.php');
            exit();
        }
    }

    include('header.php');
?>

<body>
    <div class="container">
        <div id="signupbox" style="margin-top:50px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Sign Up</div>
                    <div style="float:right; font-size: 85%; position: relative; top:-10px"><a id="signinlink" href="#"
                            onclick="$('#signupbox').hide(); $('#loginbox').show()">Sign In</a></div>
                </div>
                <div class="panel-body">
                    <form id="signupform" class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data">

                        <div style="margin-bottom: 25px" class="input-group">
                            <?php
                                if (!empty($error)) {
                                    echo "<div class='alert alert-danger'>$error</div>";
                                }
                            ?>
                        </div>

                        <div class="form-group">
                            <label for="email" class="col-md-3 control-label">Email</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="email" placeholder="Email Address">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="col-md-3 control-label">Name</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="name" placeholder="Name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sdt" class="col-md-3 control-label">Phone number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="sdt" placeholder="Phone number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="birthday" class="col-md-3 control-label">Birthday</label>
                            <div class="col-md-9">
                                <input type="date" class="form-control" name="birthday" placeholder="Birthday">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="col-md-3 control-label">Address</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="front" class="col-md-3 control-label">Mặt trước CMND</label>
                            <div class="col-md-9">
                                <input id="front_CMND" type="file" class="form-control" name="front" placeholder="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="back" class="col-md-3 control-label">Mặt sau CMND</label>
                            <div class="col-md-9">
                                <input id="back_CMND" type="file" class="form-control" name="back" placeholder="">
                            </div>
                        </div>

                        <div class="form-group">
                            <!-- Button -->
                            <div class="col-md-offset-3 col-md-9">
                                <button id="btn-signup" type="submit" class="btn btn-info"><i
                                        class="icon-hand-right"></i>
                                    &nbsp Sign Up</button>
                                <span style="margin-left:8px;">or</span>
                            </div>
                        </div>

                        <div style="border-top: 1px solid #999; padding-top:20px" class="form-group">

                            <div class="col-md-offset-3 col-md-9">
                                <button id="btn-fbsignup" type="button" class="btn btn-primary"><i
                                        class="icon-facebook"></i>   Sign Up with Facebook</button>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

