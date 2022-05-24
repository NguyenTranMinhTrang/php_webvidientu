<?php
    session_start();
    
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    require_once('./db/dbhelper.php');
    $id = $_SESSION['id'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $user = executeResult($sql, true);
    if ($user) {
        if ($user['idState'] == '00') {
            $_SESSION['first'] = true;
            header('Location: firstLogin.php');
		    exit();
        }
    }

    if ($_SESSION['chucvu'] == 'user') {
        header('Location: user/index.php');
        exit();
    }

    if ($_SESSION['chucvu'] == 'admin') {
        header('Location: admin/index.php');
        exit();
    }

    include('header.php');
?>

<body>
	<?php
	    include('navbar.php');
	?>
</body>