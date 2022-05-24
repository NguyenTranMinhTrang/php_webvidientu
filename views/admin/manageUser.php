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

    require_once('../../db/dbhelper.php');
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
            <th scope="col">CMND Mặt Trước</th>
            <th scope="col">CMND Mặt Sau</th>
            <th scope="col">Xác Minh</th>
            <th scope="col">Vô Hiệu Hóa</th>
            <th scope="col">Yêu cầu bổ sung thông tin</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $sql = "SELECT * FROM users WHERE idState != '02' and idState != '05'  ORDER BY createAT DESC";
                $listUser = executeResult($sql);
                $index = 1;
                if ($listUser) {
                    foreach ($listUser as $user) {
                        echo '<tr>
                                <th scope="row">' . ($index++) . '</th>
                                <td>' . $user['email'] . '</td>
                                <td>' . $user['username'] . '</td>
                                <td><img width="100" height="100" src="../../uploads/' .$user['front']. '"/></td>
                                <td><img width="100" height="100" src="../../uploads/' .$user['back']. '"/></td>
                                <td>
                                    <button onclick="xacminh(' . $user['id'] . ')">Xác Minh</button>
                                </td>
                                <td>
                                    <button onclick="huy(' . $user['id'] . ')">Hủy</button>
                                </td>
                                <td>
                                    <button onclick="bosung(' . $user['id'] . ')">Bổ Sung</button>
                                </td>
                            </tr> ';
                    }
                }
            ?>
             
        </tbody>
    </table>
    <script src="../main.js"></script>
</body>