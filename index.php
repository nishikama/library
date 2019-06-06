<?php

require_once('./Token.php');

// セッション変数を使うことを宣言する
session_start();
$token = new Token();
if (!$token->validateToken($_SESSION['token'])) {
    header('Location: ./login.php');
    exit();
}

session_regenerate_id(true);
$_SESSION['token'] = $token->generateToken();

// もしセッション変数に定義がある場合は、入力した内容をセットする
$lid = $_SESSION['lid'] ?? '';

// 1. DB接続します
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// 2. データ検索SQL作成
$stmt = $pdo->prepare("SELECT * FROM gs_book_table WHERE user_id = (SELECT id FROM gs_user_table WHERE lid = :lid) LIMIT 10");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

// 3. データ検索処理後
if ($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit("QueryError:" . $error[2]);
}

// エラーがない場合、以下が表示される
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>予約書籍一覧</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">予約書籍一覧</h3>
                    </div>
                    <div class="card-body">

                        <table class="table">
                            <tr>
                                <th>登録番号</th>
                                <th>書籍名</th>
                                <th>著者名</th>
                                <th>出版社名</th>
                                <th>出版日</th>
                                <th>予約取り消し</th>
                            </tr>
                            <?php
                            $i = 1;
                            if (!empty($status)) {
                                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['id'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($result['title'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($result['authors'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($result['publisher'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($result['publishedDate'], ENT_QUOTES); ?></td>
                                        <td><input type="button" data-index="index<?php echo htmlspecialchars($result['id'], ENT_QUOTES); ?>" class="delete btn-sm btn-primary" value="予約取り消し"></td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                            }
                            ?>
                        </table>
                    </div>
                    <div class="card-footer">
                        <p class="col text-center"><a href="./search.php">検索フォームへ</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script
    >
        $(() => {
            $('.delete').on('click', (e) => {
                $.ajax({
                    url: './delete.php',
                    type: 'GET',
                    data: {
                        "id": parseInt($(e.currentTarget).data('index').substr(5))
                    },
                    dataType: 'JSON'
                }).done((data, textStatus, jqXHR) => {
                    if (data.QueryError) {
                        window.alert(data.QueryError);
                    }
                    else if (data.QuerySuccess){
                        window.location.href = window.location.pathname;
                    }
                });
            });
        });
    
    </script>
</body>

</html>