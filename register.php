<?php

require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();

// トークンが存在するならログインしていることになる
if (isset($_SESSION['token'])) {
    $token = new tokenClass();
    // トークンを照合し、合致していなければログイン画面へ
    if (!isset($_SESSION['l_kanri_flg']) || $_SESSION['l_kanri_flg'] !== '1' || !$token->validateToken($_SESSION['token'])) {
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
$kanri_flg = $_SESSION['kanri_flg'] ?? '';
$life_flg = $_SESSION['life_flg'] ?? '';

// サニタイズする
$name = htmlspecialchars($name, ENT_QUOTES);
$lid = htmlspecialchars($lid, ENT_QUOTES);
$kanri_flg = htmlspecialchars($kanri_flg, ENT_QUOTES);
$life_flg = htmlspecialchars($life_flg, ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員登録</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">会員登録</h3>
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
                                    foreach ($error as $err) {

                                        // エラーメッセージを表示
                                        ?>
                                        <li><?php echo $err; ?></li>
                                    <?php

                                }
                                $_SESSION['error'] = array();
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
                                    <input type="radio" id="kanri_flg_0" name="kanri_flg" value="0"<?php echo ($kanri_flg === '0') ? ' checked' : ''; ?>>
                                    <label for="kanri_flg_0">一般者</label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="radio" id="kanri_flg_1" name="kanri_flg" value="1"<?php echo ($kanri_flg === '1') ? ' checked' : ''; ?>>
                                    <label for="kanri_flg_1">管理者</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>使用状況</label>
                                <div class="col-sm-10">
                                    <input type="radio" id="life_flg_0" name="life_flg" value="0"<?php echo ($life_flg === '0') ? ' checked' : ''; ?>>
                                    <label for="life_flg_0">使用中</label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="radio" id="life_flg_1" name="life_flg" value="1"<?php echo ($life_flg === '1') ? ' checked' : ''; ?>>
                                    <label for="life_flg_1">使用しなくなった</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-2">
                                    <input type="submit" id="submit" value="送信" class="btn btn-primary">
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
            $('#submit').on('click', (e) => {
                e.preventDefault();

                let kanri_flg = null;
                if ($('#kanri_flg_0').prop('checked') && !$('#kanri_flg_1').prop('checked')) {
                    kanri_flg = '0';
                }
                else if (!$('#kanri_flg_0').prop('checked') && $('#kanri_flg_1').prop('checked')) {
                    kanri_flg = '1';
                }

                let life_flg = null;
                if ($('#life_flg_0').prop('checked') && !$('#life_flg_1').prop('checked')) {
                    life_flg = '0';
                }
                else if (!$('#life_flg_0').prop('checked') && $('#life_flg_1').prop('checked')) {
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
                        "life_flg": life_flg
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