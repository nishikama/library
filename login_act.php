<?php

require_once('./Hash.php');
require_once('./Token.php');

// セッション変数を使うことを宣言する
session_start();
session_regenerate_id(true);

// もしセッション変数に定義がある場合は、入力した内容をセットする
$lid = $_SESSION['lid'] ?? '';
$lpw = $_SESSION['lpw'] ?? '';

// 2. DB接続します
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// 3. データ登録SQL作成
$stmt = $pdo->prepare("SELECT lpw FROM gs_user_table WHERE lid = :lid");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();
$passwd = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. データ登録処理後
header('Content-Type: application/json; charset=utf-8');
if ($status === false) {
    // SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    echo json_encode(["QueryError" => $error[2]]);
}
else {
    // SQL実行時にエラーがない場合
    $hash = new Hash();
    if ($hash->verifyPasswordHash($lpw, $passwd['lpw'])){
        $token = new Token();
        $_SESSION['token'] = $token->generateToken();
        unset($_SESSION['lpw']);
        echo json_encode(["QuerySuccess" => "login"]);
    } else{
        $_SESSION['error'] = 'ユーザーIDとパスワードが一致しません。';
        echo json_encode(["QuerySuccess" => "not login"]);
    }
}
