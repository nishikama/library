<?php

// クラス定義ファイルを呼び込む
require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();

// もしセッション変数に定義がある場合は、入力した内容をセットする
$l_lid = $_SESSION['l_lid'] ?? '';
$l_kanri_flg = $_SESSION['l_kanri_flg'] ?? '';

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
$id = $_SESSION['id'] ?? '1';
$name = $_SESSION['name'] ?? '';
$lid = $_SESSION['lid'] ?? '';
$kanri_flg = $_SESSION['kanri_flg'] ?? '0';
$life_flg = $_SESSION['life_flg'] ?? '0';

// サニタイズする
$name = htmlspecialchars($name, ENT_QUOTES);
$lid = htmlspecialchars($lid, ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員編集</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">会員編集</h3>
                        <nav class="navbar navbar-expand-md navbar-light bg-light">
                            <div class="collapse navbar-collapse justify-content-between" id="nav-set">
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./search.php">書籍検索</a></li>
                                    <li class="nav-item active"><a class="nav-link" href="./select.php">予約書籍一覧</a></li>
                                <?php

                                if ($l_kanri_flg === '1') {

                                    ?>
                                        <li class="nav-item"><a class="nav-link" href="./register.php">ユーザー登録</a></li>
                                        <li class="nav-item"><a class="nav-link" href="./users.php">ユーザー表示</a></li>
                                    </ul>
                                <?php

                            }

                            ?>
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./logout.php">ログアウト</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="card-body">
                        <?php

                        // エラーがあった場合は、エラー表示をする
                        if (!empty($_SESSION['error'])) {
                            $error = $_SESSION['error'];

                            // 赤文字にする
                            ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php

                                    // $errorは配列で、エラーメッセージがひとつずつ格納されている
                                    if (!empty($error)) {
                                        foreach ($error as $err) {

                                            // エラーメッセージを表示
                                            ?>
                                            <li><?php echo $err; ?></li>
                                        <?php
                                    }
                                }
                                $_SESSION['error'] = [];
                                ?>
                                </ul>
                            </div>
                        <?php

                    }

                    ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="name">お名前：</label>
                                <div class="col-sm-10">
                                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="lid">ログインID</label>
                                <div class="col-sm-10">
                                    <input type="text" id="lid" name="lid" value="<?php echo $lid; ?>" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="lpw">パスワード</label>
                                <div class="col-sm-10">
                                    <input type="password" id="lpw" name="lpw" value="" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>管理者権限</label>
                                <div class="col-sm-10">
                                    <input type="radio" id="kanri_flg_0" name="kanri_flg" value="0" <?php echo ($kanri_flg === '0') ? ' checked' : ''; ?>>
                                    <label for="kanri_flg_0">一般者</label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="radio" id="kanri_flg_1" name="kanri_flg" value="1" <?php echo ($kanri_flg === '1') ? ' checked' : ''; ?>>
                                    <label for="kanri_flg_1">管理者</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>使用状況</label>
                                <div class="col-sm-10">
                                    <input type="radio" id="life_flg_0" name="life_flg" value="0" <?php echo ($life_flg === '0') ? ' checked' : ''; ?>>
                                    <label for="life_flg_0">使用中</label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="radio" id="life_flg_1" name="life_flg" value="1" <?php echo ($life_flg === '1') ? ' checked' : ''; ?>>
                                    <label for="life_flg_1">使用しなくなった</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-2">
                                    <form>
                                        <input type="submit" data-direction="back" value="戻る" class="btn btn-secondary">
                                        <input type="submit" data-direction="submit" value="確認" class="btn btn-primary">
                                    </form>
                                </div>
                            </div>
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

            // こうやれば、リロードしてもアラートが出ない？かも？
            $('.btn').on('click', (e) => {
                e.preventDefault();

                let kanri_flg = null;
                if ($('#kanri_flg_0').prop('checked') && !$('#kanri_flg_1').prop('checked')) {
                    kanri_flg = '0';
                } else if (!$('#kanri_flg_0').prop('checked') && $('#kanri_flg_1').prop('checked')) {
                    kanri_flg = '1';
                }

                let life_flg = null;
                if ($('#life_flg_0').prop('checked') && !$('#life_flg_1').prop('checked')) {
                    life_flg = '0';
                } else if (!$('#life_flg_0').prop('checked') && $('#life_flg_1').prop('checked')) {
                    life_flg = '1';
                }

                $.ajax({
                    url: './post2session.php',
                    type: 'POST',
                    data: {
                        "name": $('#name').val(),
                        "lid": $('#lid').val(),
                        "lpw": $('#lpw').val(),
                        "kanri_flg": kanri_flg,
                        "life_flg": life_flg,
                        "register": false,
                        "submit": $(e.currentTarget).data('direction')
                    },
                    dataType: 'JSON'
                }).done((data, textStatus, jqXHR) => {
                    window.location.href = './confirm.php';
                });
            });
        });
    </script>
</body>

</html>