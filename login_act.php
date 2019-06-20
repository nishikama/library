<?php

// Ajax通信でのアクセスのみ実行
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    exit('このページは直接アクセスすることを許可されていません。');
}

// クラス定義ファイルを呼び込む
require_once('./hashClass.php');
require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();
session_regenerate_id(true);

// もしセッション変数に定義がある場合は、入力した内容をセットする
$lid = $_SESSION['lid'] ?? '';
$lpw = $_SESSION['lpw'] ?? '';

// DB接続
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// データ登録SQL作成
$stmt = $pdo->prepare("SELECT lpw, kanri_flg FROM gs_user_table WHERE lid = :lid AND life_flg = 0");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)

// SQL実行
$status = $stmt->execute();

// レコードを1つだけ取得
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// データ登録処理後
header('Content-Type: application/json; charset=utf-8');
if ($status === false) {
    // SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    echo json_encode(["QueryError" => $error[2]]);
} else {
    // SQL実行時にエラーがない場合
    $hash = new hashClass();
    if ($hash->verifyPasswordHash($lpw, $row['lpw'])) {
        $token = new tokenClass();
        $_SESSION['token'] = $token->generateToken();
        $_SESSION['l_lid'] = $lid;
        $_SESSION['kanri_flg'] = $row['kanri_flg'];
        unset($_SESSION['lid'], $_SESSION['lpw']);
        echo json_encode(["VerifySuccess" => "login"]);
    } else {
        $_SESSION['error'] = 'ユーザーIDとパスワードが一致しません。';
        echo json_encode(["VerifyError" => "not login"]);
    }
}
