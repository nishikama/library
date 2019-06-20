-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 
-- サーバのバージョン： 10.1.40-MariaDB
-- PHP Version: 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gs_db3`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_an_table`
--

CREATE TABLE `gs_an_table` (
  `id` int(12) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `naiyou` text COLLATE utf8_unicode_ci,
  `indate` datetime NOT NULL,
  `age` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `gs_an_table`
--

INSERT INTO `gs_an_table` (`id`, `name`, `email`, `naiyou`, `indate`, `age`) VALUES
(1, 'A', 'A', '内容', '2018-09-22 07:28:23', 10),
(2, '織田信成', 'test1@test.jp', 'メモ', '2018-09-22 16:02:47', 20),
(3, '徳川1家康', 'test2@test.jp', 'メモ', '2018-09-22 16:06:42', 30),
(4, '伊達政宗', 'test4@test.jp', 'メモ', '2018-09-22 16:07:48', 30),
(5, 'アメリカ・ワシントン', 'test5@test.jp', 'メモ', '2018-09-22 16:07:48', 40),
(6, 'ディカプリオ', 'test6@test.jp', 'メモ', '2018-09-22 16:07:48', 40),
(7, '山田太郎', 'yamada@test.jp', 'テスト', '2018-09-22 17:14:36', 20),
(8, 'aaaaa', 'aaaaaaaaaa', 'aaaaaaaa', '2018-09-22 17:59:31', 10),
(9, 'Daisuke Yamazaki', 'php.yamazaki@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaa', '2018-09-22 18:13:28', 20),
(10, 'Yamazaki Daisuke', 'php.yamazaki@gmail.com', 'TSET', '2018-09-29 05:19:42', 20),
(11, 'TEST', 'TEST', 'ETst', '2018-09-29 05:20:05', 20);

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_book_table`
--

CREATE TABLE `gs_book_table` (
  `id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `authors` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `publisher` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `publishedDate` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `reserveDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `gs_book_table`
--

INSERT INTO `gs_book_table` (`id`, `title`, `authors`, `publisher`, `publishedDate`, `reserveDate`, `user_id`) VALUES
(1, '要領よくマスターしたもの勝ち社会福祉士・精神保健福祉士国家試験・共通問題', '社会福祉士国家試験研究会', '', '2007-06-30', '2019-06-18 10:51:10', 1),
(3, '死ぬまでに読んでおきたい　太宰治', '太宰治', 'ユナイテッド・ブックス', '', '2019-06-18 10:51:10', 1);

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_user_table`
--

CREATE TABLE `gs_user_table` (
  `id` int(12) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `lid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `lpw` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `kanri_flg` int(1) NOT NULL,
  `kanri_hash` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `life_flg` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `gs_user_table`
--

INSERT INTO `gs_user_table` (`id`, `name`, `lid`, `lpw`, `kanri_flg`, `kanri_hash`, `life_flg`) VALUES
(1, 'システム管理者', 'admin', '$2y$10$hi6rBdkcgGyzSsAaU4QMGuvXbuCC/JNFelIlZq9/Fp0T6RTAnSgqe', 1, '19417a7101be0c8cd5542196eb0ea8989e900ba41919629ee4993653d7e9f1b5', 0),
(4, '西川 真', 'nishikama', '$2y$10$1xU71DGcpuvxpjyA9WtNUOCTzOk6/Dom0vvqDl/D6xrUTJnFbAIJi', 0, '3f54dd7990ff5293290fbb301761fca97ec819935ec40ba488e8598f62b8012e', 0),
(5, '西川 紀実子', 'kimitty', '$2y$10$zg9gzaIuaZriEThAcyo/f.5aqDp9BkbUrL/QdMw.XsuID/PPcCRHW', 0, '981af14365e91cfab0630140317ae99521110b38051b80edf893afa701bbab1f', 0),
(6, '西川 結貴', 'yuki', '$2y$10$LGFdiigDtUK.fYJV76i.AuLQlFKAjxKg2KygeW0bLsi8ZF7H.jmhe', 0, 'b79cfd454ccf278f688c168fc9835e9f420d4d8e0bdde1ffb802f86a2b98e0fa', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gs_an_table`
--
ALTER TABLE `gs_an_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gs_book_table`
--
ALTER TABLE `gs_book_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `gs_user_table`
--
ALTER TABLE `gs_user_table`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gs_an_table`
--
ALTER TABLE `gs_an_table`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `gs_book_table`
--
ALTER TABLE `gs_book_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gs_user_table`
--
ALTER TABLE `gs_user_table`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `gs_book_table`
--
ALTER TABLE `gs_book_table`
  ADD CONSTRAINT `gs_books_table_fk` FOREIGN KEY (`user_id`) REFERENCES `gs_user_table` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
