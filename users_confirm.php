<?php

// クラス定義ファイルを呼び込む
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

// もしセッション変数に定義がある場合は、入力した内容をセットする
$name = $_SESSION['name'] ?? '';
$lid = $_SESSION['lid'] ?? '';
$lpw = $_SESSION['lpw'] ?? '';
$kanri_flg = $_SESSION['kanri_flg'] ?? '';
$life_flg = $_SESSION['life_flg'] ?? '';

// サニタイズする
$name = htmlspecialchars($name, ENT_QUOTES);
$lid = htmlspecialchars($lid, ENT_QUOTES);
$lpw = htmlspecialchars($lpw, ENT_QUOTES);
$kanri_flg = htmlspecialchars($kanri_flg, ENT_QUOTES);
$life_flg = htmlspecialchars($life_flg, ENT_QUOTES);

// エラーがない状態にセット（空配列）
$error = [];

if ($name === '') {

    // エラーメッセージを追加
    $error[] = 'お名前が入力されていません';
} elseif (mb_strlen($name) > 20) {

    // エラーメッセージを追加
    $error[] = 'お名前は20文字以内で入力してください';
}

if ($lid === '') {

    // エラーメッセージを追加
    $error[] = 'ユーザーIDが入力されていません';
} elseif (!preg_match('/^[ -~｡-ﾟ]{1,20}$/', $lid)) {

    // エラーメッセージを追加
    $error[] = 'ユーザーIDは半角のみで20文字以内で入力してください';
}

if ($lpw === '') {

    // エラーメッセージを追加
    $error[] = 'パスワードが入力されていません';
} elseif (!preg_match('/^[ -~｡-ﾟ]{1,20}$/', $lpw)) {

    // エラーメッセージを追加
    $error[] = 'パスワードは半角のみで20文字以内で入力してください';
}

if ($kanri_flg !== '0' && $kanri_flg !== '1') {

    // エラーメッセージを追加
    $error[] = '管理者権限はいずれかにチェックしてください';
}

if ($life_flg !== '0' && $life_flg !== '1') {

    // エラーメッセージを追加
    $error[] = '使用状況はいずれかにチェックしてください';
}

// セッション変数に値を格納
$_SESSION['name'] = $name;
$_SESSION['lid'] = $lid;
$_SESSION['lpw'] = $lpw;
$_SESSION['kanri_flg'] = $kanri_flg;
$_SESSION['life_flg'] = $life_flg;

// どれかひとつでも規定外だったらifしてください
if (!empty($error)) {

    // 配列をセッション変数に格納
    $_SESSION['error'] = $error;

    //初めのフォームに飛ぶ
    header('Location: ./edit.php');
    exit();
}

// エラーがない場合、以下が表示される
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ユーザー登録</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">確認画面</h3>
                    </div>
                    <div class="card-body">
                        <p>誤りがないことを確認のうえ完了ボタンをクリックしてください。</p>

                        <table class="table">
                            <tr>
                                <th>お名前：</th>
                                <td><?php echo $name; ?></td>
                            </tr>

                            <tr>
                                <th>ログインID：</th>
                                <td><?php echo $lid; ?></td>
                            </tr>

                            <tr>
                                <th>管理者権限：</th>
                                <td><?php echo ($kanri_flg) ? '管理者' : '一般者'; ?></td>
                            </tr>

                            <tr>
                                <th>使用状況：</th>
                                <td><?php echo ($life_flg) ? '使用しなくなった' : '使用中'; ?></td>
                            </tr>
                        </table>
                        <form method="post">
                            <input type="submit" data-direction="back" value="戻る" class="btn btn-secondary">
                            <input type="submit" data-direction="submit" value="完了" class="btn btn-primary">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script>
        $(() => {

            // 戻るボタンか完了ボタンを押したときの処理
            $('.btn').on('click', (e) => {
                e.preventDefault();
                $.ajax({
                    url: './post2session.php',
                    type: 'POST',
                    data: {
                        "submit": $(e.currentTarget).data('direction')
                    },
                    dataType: 'JSON'
                }).done((data, textStatus, jqXHR) => {
                    window.location.href = './users_complete.php';
                });
            });
        });
    </script>
</body>

</html>