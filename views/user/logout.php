<?php
    require_once ('../../api/authen.php');

    $data = logout();
    if ($data) {
        if ($data['code'] == 1) {
            header('Location: views/index.php');
            exit();
        }
    }

?>