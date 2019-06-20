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

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員一覧</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">会員一覧</h3>
                        <nav class="navbar navbar-expand-md navbar-light bg-light">
                            <div class="collapse navbar-collapse justify-content-between" id="nav-set">
                                <ul class="navbar-nav">
                                    <li class="nav-item"><a class="nav-link" href="./logout.php">ログアウト</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="card-body">

                        <div id="results"></div>
                        <div id="pager"></div>

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
    <script>
        let page = 1;
        $(() => {

            $.ajax({
                url: './post2session.php',
                type: 'POST',
                data: {
                    "page": page
                },
                dataType: 'JSON'
            }).done((session, textStatus, jqXHR) => {
                $.ajax({
                    url: './users_act.php',
                    dataType: 'JSON'
                }).done((usersData, textStatus, jqXHR) => {

                    let $nav = $('<ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="./search.php">書籍検索</a></li><li class="nav-item active"><a class="nav-link" href="./select.php">予約書籍一覧</a></li></ul>');
                    if (usersData[0].kanri_flg === '1') {
                        $nav.append('<li class="nav-item"><a class="nav-link" href="./register.php">ユーザー登録</a></li><li class="nav-item"><a class="nav-link" href="./users.php">ユーザー表示</a></li>');
                    }
                    $('#nav-set').prepend($nav);

                    let $table = $('<table class="table">');
                    $table.append('<tr><th>お名前</th><th>ログインID</th><th>管理者権限</th><th>使用状況</th><th>操作</th></tr>');
                    $.each(usersData, (i, item) => {
                        $table.append(`<tr><td>${item.name ? item.name : ''}</td><td>${item.lid ? item.lid : ''}</td><td>${(item.kanri_flg === '1') ? '管理者' : '一般者'}</td><td>${(item.life_flg === '1') ? '使用しなくなった' : '使用中'}</td><td><input type="submit" data-index="index${i + 1}" class="edit btn-sm btn-primary" value="編集">　<input type="submit" data-index="index${i + 1}" class="delete btn-sm btn-primary" value="削除"></td></tr>`);
                    });
                    $('#results').html($table);

                    let prev = '';
                    let next = '';
                    if (page > 1) {
                        prev = '<a id="prev" href="javascript:void(0);">前へ</a>';
                    }
                    if (page < Math.ceil(usersData.length / 10)) {
                        next = '<a id="next" href="javascript:void(0);">次へ</a>';
                    }
                    if (page > 1 && page < Math.ceil(usersData.length / 10)) {
                        $('#pager').html(Math.ceil(usersData.length / 10) + 'ページ中 ' + page + 'ページ　' + prev + '｜' + next);
                    } else {
                        $('#pager').html(Math.ceil(usersData.length / 10) + 'ページ中 ' + page + 'ページ　' + prev + next);
                    }

                    $(document).on('click', '#prev', (e) => {
                        e.preventDefault();
                        page--;
                        $('#search').trigger('click');
                    });

                    $(document).on('click', '#next', (e) => {
                        e.preventDefault();
                        page++;
                        $('#search').trigger('click');
                    });

                    $('.edit').on('click', (e) => {
                        e.preventDefault();
                        const index = parseInt($(e.currentTarget).data('index').substr(5)) - 1;
                        $.ajax({
                            url: './post2session.php',
                            type: 'POST',
                            data: {
                                "id": usersData[index].id,
                                "name": usersData[index].name,
                                "lid": usersData[index].lid,
                                "kanri_flg": usersData[index].kanri_flg,
                                "life_flg": usersData[index].life_flg
                            },
                            dataType: 'JSON'
                        }).done((result, textStatus, jqXHR) => {
                            if (result) {
                                window.location.href = './edit.php';
                            }
                        });
                    });
                }).fail((jqXHR, textStatus, errorThrown) => {
                    console.log(textStatus);
                });
            });
        });
    </script>
</body>

</html>