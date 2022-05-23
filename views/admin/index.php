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

    include ('../header.php');
?>

<body>
    <?php  include('./menu.php') ?>
</body>