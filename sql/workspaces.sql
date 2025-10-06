-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-10-06 02:43:40
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `corelista`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `workspaces`
--

CREATE TABLE `workspaces` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `workspaces`
--

INSERT INTO `workspaces` (`id`, `name`, `created_at`) VALUES
(1, 'テストワークスペース', '2025-08-28 09:30:53'),
(2, '<h1>test</h1>', '2025-08-28 13:44:58'),
(5, '2', '2025-08-29 14:37:12'),
(8, '3', '2025-09-09 09:41:12'),
(9, '4', '2025-09-09 09:41:39'),
(10, '5', '2025-09-09 09:56:07'),
(11, '6', '2025-09-09 09:56:26'),
(12, '7', '2025-09-09 09:56:29'),
(13, '8', '2025-09-09 09:56:33'),
(14, '9', '2025-09-09 09:57:20'),
(15, '1234', '2025-09-09 11:13:32');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `workspaces`
--
ALTER TABLE `workspaces`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `workspaces`
--
ALTER TABLE `workspaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
