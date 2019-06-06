<?php

// セッション変数を使うことを宣言する
session_start();
session_regenerate_id(true);

if (isset($_POST)) {
    foreach ($_POST as $key => $value) {
        $_SESSION[$key] = $value;
    }
    $_POST = array();
    $json = array('result' => true);
}
else {
    $json = array('result' => false);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($json);

