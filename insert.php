<?php

require_once('tokenClass.php');

// Ajax通信でのアクセスのみ実行
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    exit('このページは直接アクセスすることを許可されていません。');
}

// セッション変数を使うことを宣言する
session_start();

// トークンを照合し、合致していなければログイン画面へ
$token = new tokenClass();
if (!$token->validateToken($_SESSION['token'])) {
    header('Location: ./login_act.php');
    exit();
}

// 合致していれば新しくトークンを発行
session_regenerate_id(true);
$_SESSION['token'] = $token->generateToken();

// POSTデータ取得
$title = $_POST['title'] ?? '';
$authors = $_POST['authors'] ?? '';
$publisher = $_POST['publisher'] ?? '';
$publishedDate = $_POST['publishedDate'] ?? '';
$lid = $_SESSION['lid'] ?? '';

// DB接続
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO gs_book_table( id, title, authors, publisher, publishedDate, user_id) VALUES( null, :title, :authors, :publisher, :publishedDate, (SELECT id FROM gs_user_table WHERE lid = :lid))");
$stmt->bindValue(':title', $title, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':authors', $authors, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':publisher', $publisher, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':publishedDate', $publishedDate, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)

// SQL実行
$status = $stmt->execute();

// データ登録処理後

if ($status === false) {
    // SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    echo json_encode(["QueryError" => $error[2]]);
} else {
    // SQL実行時にエラーがない場合
    echo json_encode(["QuerySuccess" => "正常に保存されました"]);
}