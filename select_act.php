<?php

// Ajax通信でのアクセスのみ実行
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    exit('このページは直接アクセスすることを許可されていません。');
}

require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();

// トークンが存在するならログインしていることになる
if (isset($_SESSION['token'])) {
    $token = new tokenClass();
    // トークンを照合し、合致していなければログイン画面へ
    if (!$token->validateToken($_SESSION['token'])) {
        // header('Location: ./login.php');
        // exit();
    }

    // 合致していれば新しくトークンを発行
    session_regenerate_id(true);
    $_SESSION['token'] = $token->generateToken();
}

// もしセッション変数に定義がある場合は、入力した内容をセットする
$lid = $_SESSION['lid'] ?? '';
$page = $_SESSION['page'] ?? 1;

// DB接続
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// データ検索SQL作成
$stmt = $pdo->prepare("SELECT * FROM gs_book_table b, gs_user_table u WHERE u.lid = :lid AND b.user_id = u.id AND u.life_flg = 0 LIMIT 10 OFFSET :index");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':index', (intval($page) - 1) * 10, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

$reservedata = [];
$firstLoop = true;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($firstLoop) {
        $_SESSION['l_kanri_flg'] = $row['kanri_flg'];
        unset($_SESSION['lid']);
        $firstLoop = false;
    }
    $reservedata[] = $row;
}

// データ登録処理後
header('Content-Type: application/json; charset=utf-8');
if ($status === false) {
    // SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    echo json_encode(["QueryError" => $error[2]]);
} else {
    // SQL実行時にエラーがない場合
    echo json_encode($reservedata);
}