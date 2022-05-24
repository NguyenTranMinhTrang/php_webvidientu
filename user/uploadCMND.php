<?php
    session_start();
    if (!isset($_SESSION['id'])) {
        header('Location: ../login.php');
        exit();
    }

    if ($_SESSION['state'] != '06') {
        header('Location: index.php');
        exit();
    }

    require_once('../db/dbhelper.php');
    require_once('../utils/utility.php');
    $error = "";

    if (isset($_POST['upload']) ) {
        $id = $_SESSION['id'];
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $user = executeResult($sql, true);
        $email = $user['email'];
        $front = $user['front'];
        $back = $user['back'];

        if ($user) {
            $resultImageFront = uploadAgain($email, "front", $front);
            if ($resultImageFront['code'] == 0) {
                $error = $resultImageFront['error'];
            }
            else {
                $resultImageBack = uploadAgain($email, "back", $back);
                if ($resultImageBack['code'] == 0) {
                    $error = $resultImageBack['error'];
                }
                else {
                    $frontUrl = $resultImageFront['tmp'];
                    $backUrl = $resultImageBack['tmp'];
                    $sql = "UPDATE users SET front = '$frontUrl' , back = '$backUrl', idState = '01' WHERE id = '$id'";
                    execute($sql);
                    header('Location: index.php');
                    exit();
                }
            }
        }
    }
    include ('../header.php');
?>

<body>
    <div class="container">
        <div id="signupbox" style="margin-top:50px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Upload CMND again</div>
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
                        <input hidden type="text"  name="upload" value="uploadFileAgain">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>