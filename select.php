<?php

// クラス定義ファイルを呼び込む
require_once('./tokenClass.php');

// セッション変数を使うことを宣言する
session_start();

// もしセッション変数に定義がある場合は、入力した内容をセットする
$l_lid = $_SESSION['l_lid'] ?? '';
$l_kanri_flg = $_SESSION['l_kanri_flg'] ?? '';

// トークンが存在するならログインしていることになる
if (!isset($_SESSION['token'])) {
    header('Location: ./login.php');
    exit();
}

// DB接続
try {
    $pdo = new PDO('mysql:dbname=gs_db3;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('データベースに接続できませんでした。' . $e->getMessage());
}

// データ検索SQL作成
$stmt = $pdo->prepare("SELECT kanri_flg, kanri_hash FROM gs_user_table WHERE lid = :l_lid AND life_flg = 0");
$stmt->bindValue(':l_lid', $l_lid, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$kanri_flg = $row['kanri_flg'];
$kanri_hash = $row['kanri_hash'];

$token = new tokenClass();
// トークンを照合し、合致していなければログイン画面へ
if (($kanri_flg === '1' && $kanri_hash !== hash('sha256', $l_lid . 'administrator')) || ($kanri_flg === '0' && $kanri_hash !== hash('sha256', $l_lid . 'user')) || !$token->validateToken($_SESSION['token'])) {
    header('Location: ./login.php');
    exit();
}

// 合致していれば新しくトークンを発行
session_regenerate_id(true);
$_SESSION['token'] = $token->generateToken();

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
                                    <?php

                                }

                                ?>
                                </ul>
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./logout.php">ログアウト</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>書籍名</th>
                                    <th>著者名</th>
                                    <th>出版社名</th>
                                    <th>出版日</th>
                                    <th>予約日</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="results"></tbody>
                            <tfoot id="pager"></tfoot>
                        </table>
                    </div>
                    <div class="card-footer">
                        <p class="col text-center"><a href="./search.php">書籍検索へ</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script>
        let page = 1;
        $(() => {

            // 書籍一覧を表示する関数
            const viewTable = (() => {
                $.ajax({
                    url: './post2session.php',
                    type: 'POST',
                    data: {
                        "page": page
                    },
                    dataType: 'JSON'
                }).done((session, textStatus, jqXHR) => {

                    $('#results').empty();
                    $.ajax({
                        url: './select_act.php',
                        dataType: 'JSON'
                    }).done((reserveData, textStatus, jqXHR) => {

                        if (reserveData.length) {
                            $.each(reserveData, (i, item) => {
                                $('#results').append(`<tr><td>${item.title ? item.title : ''}</td><td>${item.authors ? item.authors : ''}</td><td>${item.publisher ? item.publisher : ''}</td><td>${item.publishedDate ? item.publishedDate : ''}</td><td>${item.reserveDate ? item.reserveDate : ''}</td><td><input type="submit" data-index="index${item.id ? item.id : ''}" class="cancel btn-sm btn-primary" value="予約を取り消す"></td></tr>`);
                            });
                        }

                        let prev = '';
                        let next = '';

                        const count = Math.ceil(reserveData.length / 10) ? Math.ceil(reserveData.length / 10) : 0;

                        if (page > 1) {
                            prev = '<a id="prev" href="javascript:void(0);">前へ</a>';
                        }
                        if (page < count) {
                            next = '<a id="next" href="javascript:void(0);">次へ</a>';
                        }
                        if (page > 1 && page < count) {
                            $('#pager').html(count + 'ページ中 ' + page + 'ページ　' + prev + '｜' + next);
                        } else {
                            $('#pager').html(count + 'ページ中 ' + page + 'ページ　' + prev + next);
                        }

                        $(document).on('click', '#prev', (e) => {
                            e.preventDefault();
                            page--;
                            $(e.currentTarget).trigger('click');
                        });

                        $(document).on('click', '#next', (e) => {
                            e.preventDefault();
                            page++;
                            $(e.currentTarget).trigger('click');
                        });

                        $('.cancel').on('click', (e) => {
                            e.preventDefault();
                            const id = parseInt($(e.currentTarget).data('index').substr(5));
                            if (window.confirm('予約を取り消しますか？')) {
                                $.ajax({
                                    url: './cancel.php',
                                    type: 'GET',
                                    data: {
                                        "id": id
                                    },
                                    dataType: 'JSON'
                                }).done((result, textStatus, jqXHR) => {
                                    if (result.QueryError) {
                                        window.alert(result.QueryError);
                                    } else if (result.QuerySuccess) {
                                        window.alert(result.QuerySuccess);
                                    }
                                }).fail((jqXHR, textStatus, errorThrown) => {
                                    $('#results').text('エラーが発生しました。ステータス：' + jqXHR.status);
                                });
                                viewTable();
                            }
                        });
                    }).fail((jqXHR, textStatus, errorThrown) => {
                        $('#results').text('エラーが発生しました。ステータス：' + jqXHR.status);
                    });
                });
                viewTable();
            });
        });
    </script>
</body>

</html>