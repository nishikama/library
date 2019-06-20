<?php

// Ajax通信でのアクセスのみ実行
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    exit('このページは直接アクセスすることを許可されていません。');
}

// 1. GETデータ取得
$id = $_GET['id'] ?? null;

// 2. DB接続します
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// 3. データ登録SQL作成
$stmt = $pdo->prepare("DELETE FROM gs_book_table WHERE id = :id");
$stmt->bindValue(':id', intval($id), PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

// 4. データ登録処理後
header('Content-Type: application/json; charset=utf-8');
if ($status === false) {
    // SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    echo json_encode(["QueryError" => $error[2]]);
}
else {
    // SQL実行時にエラーがない場合
    echo json_encode(["QuerySuccess" => "正常に取り消されました"]);
}
