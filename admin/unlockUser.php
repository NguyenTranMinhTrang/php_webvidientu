<?php

    session_start();
    if (!isset($_SESSION['id'])) {
        header('Location: ../login.php');
        exit();
    }

    if ($_SESSION['chucvu'] == 'user') {
        header('Location: ../user/index.php');
        exit();
    }

    require_once('../db/dbhelper.php');
    include ('../header.php');
?>

<body>
    <h1>Danh sách tài khoản user</h1>
    <table class="table table-dark">
        <thead>
            <tr>
                <th scope="col">Stt</th>
                <th scope="col">Email</th>
                <th scope="col">Username</th>
                <th scope="col">Mở Khóa</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $sql = "SELECT * FROM users WHERE idState = '03'  ORDER BY createAT DESC";
                $listUser = executeResult($sql);
                $index = 1;
                if ($listUser) {
                    foreach ($listUser as $user) {?>
                        <tr>
                            <th scope="row"><?=$index++?></th>
                            <td><?=$user['email']?></td>
                            <td><?=$user['username']?></td>
                            <td>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dialog1-<?=$user['id']?>">
                                Mở Khóa
                            </button>
                            </td>
                        </tr>
                        
                        <div class="modal fade" id="dialog1-<?=$user['id']?>" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                
                                    <div class="modal-header">
                                        <h5 class="modal-title">Xác Nhận</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    
                                    <div class="modal-body">
                                        Bạn muốn mở khóa tài khoản này ?
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                        <button type="button" class="btn btn-primary" onclick="mokhoa(<?=$user['id']?>)" data-bs-dismiss="modal">Yes</button>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
             
        </tbody>
    </table>
    <script src="../main.js"></script>
</body>