<?php

// クラス定義ファイルを呼び込む
require_once('./hashClass.php');
require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();

// トークンが存在するならログインしていることになる
if (isset($_SESSION['token'])) {

    // DB接続
    try {
        $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
    } catch (PDOException $e) {
        exit('データベースに接続できませんでした。' . $e->getMessage());
    }

    // データ検索SQL作成
    $stmt = $pdo->prepare("SELECT kanri_hash FROM gs_user_table WHERE lid = :l_lid");
    $stmt->bindValue(':l_lid', $_SESSION['l_lid'], PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $kanri_hash = $row['kanri_hash'];

    $token = new tokenClass();
    // トークンを照合し、合致していなければログイン画面へ
    if (!isset($_SESSION['l_lid']) || $kanri_hash !== hash('sha256', $_SESSION['l_lid'] . 'administrator') || !$token->validateToken($_SESSION['token'])) {
        header('Location: ./login.php');
        exit();
    }

    // 合致していれば新しくトークンを発行
    session_regenerate_id(true);
    $_SESSION['token'] = $token->generateToken();
}

// 1. POSTデータ取得
$id = $_SESSION['id'] ?? '1';
$name = $_SESSION['name'] ?? '';
$lid = $_SESSION['lid'] ?? '';
$lpw = $_SESSION['lpw'] ?? '';
$kanri_flg = $_SESSION['kanri_flg'] ?? '0';
$life_flg = $_SESSION['life_flg'] ?? '0';
$register = $_SESSION['register'] ?? false;

if ($_SESSION['submit'] === 'back') {
    if ($register) {
        header('Location: ./register.php');
    } else {
        header('Location: ./edit.php');
    }
    exit();
}

// 2. DB接続します
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// 3. データ登録SQL作成
$hash = new hashClass();
if ($register) {
    $stmt = $pdo->prepare("INSERT INTO gs_user_table (id, name, lid, lpw, kanri_flg, kanri_hash, life_flg) VALUES(null, :name, :lid, :lpw, :kanri_flg, sha2(CONCAT(lid, :kanri_hash), 256), :life_flg)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':lpw', $hash->generatePasswordHash($lpw), PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':kanri_flg', $kanri_flg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':kanri_hash', ($kanri_flg === '1') ? 'administrator' : 'user', PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':life_flg', $life_flg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
} else {
    $stmt = $pdo->prepare("UPDATE gs_user_table SET name = :name, lid = :lid, lpw = lpw, kanri_flg = :kanri_flg, kanri_hash = sha2(CONCAT(lid, :kanri_hash), 256), life_flg = :life_flg WHERE id = :id");
    $stmt->bindValue(':id', intval($id), PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':lpw', $hash->generatePasswordHash($lpw), PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':kanri_flg', $kanri_flg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':kanri_hash', ($kanri_flg === '1') ? 'administrator' : 'user', PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':life_flg', $life_flg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)    
}

$status = $stmt->execute();var_dump($id)

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>登録完了</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">登録完了しました。</h3>
                        <nav class="navbar navbar-expand-md navbar-light bg-light">
                            <div class="collapse navbar-collapse justify-content-between" id="nav-set">
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./search.php">書籍検索</a></li>
                                    <li class="nav-item active"><a class="nav-link" href="./select.php">予約書籍一覧</a></li>
                                    <?php

                                    if ($_SESSION['l_kanri_flg'] === '1') {

                                        ?>
                                        <li class="nav-item"><a class="nav-link" href="./register.php">ユーザー登録</a></li>
                                        <li class="nav-item"><a class="nav-link" href="./users.php">ユーザー表示</a></li>
                                    </ul>
                                <?php

                            }
                            unset($_SESSION['id'], $_SESSION['name'], $_SESSION['lid'], $_SESSION['lpw'], $_SESSION['kanri_flg'], $_SESSION['life_flg'], $_SESSION['register']);

                            ?>
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./logout.php">ログアウト</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="card-body">
                        <p class="col text-center"><a href="./users.php">会員一覧へ戻る</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>