<?php
    require_once('../../db/dbhelper.php');
    if ($_SERVER['REQUEST_METHOD'] != 'POST'){
        die(json_encode(array('code' => 4, 'data' => 'Only POST method is supported')));
    }

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if ($contentType !== "application/json") {
        die(json_encode(array('code' => 4, 'data' => 'Content-Type is not set as "application/json"')));
    }
    $content = file_get_contents('php://input');
    
    $data=json_decode($content);

    if(is_null($data)){
        die(json_encode(array('code' => 2, 'data' => 'Only json is supported')));
    }

    $id = $data -> id;
    $sql = "SELECT * FROM times_login WHERE id = '$id'";
    $data = executeResult($sql, true);
    if ($data) {
        $state = $data['oldState'];
        $sql = "UPDATE users SET idState= '$state' WHERE id = '$id'";
        execute($sql);
        $sql = "DELETE FROM times_login WHERE id = '$id'";
        execute($sql);
        die(json_encode(array('code' => 1, 'data' => 'Success')));
    }
    else {
        die(json_encode(array('code' => 0, 'data' => 'There is an error occurd')));
    }        

?>