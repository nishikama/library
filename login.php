<?php

// クラス定義ファイルを呼び込む
require_once('./tokenClass.php');
require_once('./logoutClass.php');

// セッション変数を使うことを宣言する
session_start();

// トークンが存在するならログインしていることになるので、いったんログアウトさせる
if (isset($_SESSION['token'])) {
    $logout = new logoutClass();
    $logout->execute();
}

// もしセッション変数に定義がある場合は、入力した内容をセットする
$lid = $_SESSION['lid'] ?? '';
$error = $_SESSION['error'] ?? '';

// サニタイズする
$lid = htmlspecialchars($lid, ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ログイン</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">ログイン</h3>
                    </div>
                    <div class="card-body">
                        <?php

                        // エラーがあった場合は、エラー表示をする
                        if (!empty($error)) {

                            // 赤文字にする
                            ?>
                            <div class="alert alert-danger">
                                <p><?php echo $error; ?></p>
                            </div>
                        <?php

                    }

                    ?>
                        <form method="POST">
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
                                <div class="col-sm-10 col-sm-offset-2">
                                    <input type="submit" id="submit" value="送信" class="btn btn-primary">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <p class="col text-center"><a href="./register.php">会員登録へ</a></p>
                        <p class="col text-center"><a href="./search.php">検索フォームへ</a></p>
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
                $.ajax({
                    url: './post2session.php',
                    type: 'POST',
                    data: {
                        "lid": $('#lid').val(),
                        "lpw": $('#lpw').val()
                    },
                    dataType: 'JSON'
                }).done((session, textStatus, jqXHR) => {
                    $.ajax({
                        url: './login_act.php',
                        dataType: 'JSON'
                    }).done((result, textStatus, jqXHR) => {
                        if (result.VerifySuccess === 'login') {
                            window.location.href = './select.php';
                        } else {
                            window.location.reload();
                        }
                    })
                });
            });
        });
    </script>
</body>

</html>