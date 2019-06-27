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
    $token = new tokenClass();
    // トークンを照合し、合致していなければログイン画面へ
    if (!$token->validateToken($_SESSION['token'])) {
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
    <title>書籍検索</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="col text-center">書籍検索</h3>
                        <nav class="navbar navbar-expand-md navbar-light bg-light">
                            <div class="collapse navbar-collapse justify-content-between" id="nav-set">
                                <ul class="navbar-nav">
                                    <li class="nav-item active"><a class="nav-link" href="./search.php">書籍検索</a></li>
                                    <li class="nav-item"><a class="nav-link" href="./select.php">予約書籍一覧</a></li>
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
                                <?php

                                if (isset($_SESSION['token'])) {

                                    ?>

                                    <li class="nav-item"><a class="nav-link" href="./logout.php">ログアウト</a></li>
                                <?php
                            } else {
                                ?>
                                    <li class="nav-item"><a class="nav-link" href="./login.php">ログイン</a></li>
                                <?php
                            }

                            ?>
                            </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="bookKeywords">キーワード：</label>
                                <div class="col-sm-10">
                                    <input type="text" id="bookKeywords" name="bookKeywords" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-2">
                                    <input type="submit" id="search" value="検索" class="btn btn-primary">
                                </div>
                            </div>

                            <div id="results"></div>
                            <div id="pager"></div>

                        </form>
                    </div>
                    <?php
                    if (isset($_SESSION['token'])) {
                        ?>
                        <div class="card-footer">
                            <p class="col text-center"><a href="./select.php">予約書籍一覧へ</a></p>
                        </div>
                    <?php
                }
                ?>
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

            $('#search').on('click', (e) => {
                e.preventDefault();
                $.ajax({
                    url: './getBookdata.php',
                    type: 'GET',
                    data: {
                        "bookKeywords": $('#bookKeywords').val(),
                        "page": page
                    },
                    dataType: 'JSON'
                }).done((bookData, textStatus, jqXHR) => {
                    let $table = $('<table class="table">');
                    $table.append('<tr><th>書籍名</th><th>著者名</th><th>出版社名</th><th>出版日</th><?php if (isset($_SESSION['token'])) echo '<th>操作</th>'; ?></tr>');
                    let authors = [];
                    $.each(bookData.items, (i, item) => {
                        authors[i] = '';
                        $.each(item.volumeInfo.authors, (j, author) => {
                            authors[i] += author ? author : '';
                            if (j < item.volumeInfo.authors.length - 1) {
                                authors[i] += ', ';
                            }
                        })
                        $table.append(`<tr><td>${item.volumeInfo.title ? item.volumeInfo.title : ''}</td><td>${authors[i]}</td><td>${item.volumeInfo.publisher ? item.volumeInfo.publisher : ''}</td><td>${item.volumeInfo.publishedDate ? item.volumeInfo.publishedDate : ''}</td><?php if (isset($_SESSION['token'])) echo '<td><input type="submit" data-index="index${i + 1}" class="reserve btn-sm btn-primary" value="予約する"></td>'; ?></tr>`);
                    });
                    $('#results').html($table);

                    let prev = '';
                    let next = '';
                    if (page > 1) {
                        prev = '<a id="prev" href="javascript:void(0);">前へ</a>';
                    }
                    if (page < bookData.totalItems) {
                        next = '<a id="next" href="javascript:void(0);">次へ</a>';
                    }
                    if (page > 1 && page < bookData.totalItems) {
                        $('#pager').html(Math.ceil(bookData.totalItems / 10) + 'ページ中 ' + page + 'ページ　' + prev + '｜' + next);
                    } else {
                        $('#pager').html(Math.ceil(bookData.totalItems / 10) + 'ページ中 ' + page + 'ページ　' + prev + next);
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

                    $('.reserve').on('click', (e) => {
                        e.preventDefault();
                        const index = parseInt($(e.currentTarget).data('index').substr(5)) - 1;
                        $.ajax({
                            url: './insert.php',
                            type: 'POST',
                            data: {
                                "title": bookData.items[index].volumeInfo.title,
                                "authors": authors[index],
                                "publisher": bookData.items[index].volumeInfo.publisher,
                                "publishedDate": bookData.items[index].volumeInfo.publishedDate
                            },
                            dataType: 'JSON'
                        }).done((result, textStatus, jqXHR) => {
                            if (result.QueryError) {
                                window.alert(result.QueryError);
                            } else if (result.QuerySuccess) {
                                window.alert(result.QuerySuccess);
                            }
                        });
                    });
                });
            });
        });
    </script>
</body>

</html>