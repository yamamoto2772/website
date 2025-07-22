-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql325.phy.lolipop.lan
-- 生成日時: 2025 年 7 月 15 日 10:57
-- サーバのバージョン： 8.0.35
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `LAA1617951-team4`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `deliverables`
--

CREATE TABLE `deliverables` (
  `成果ID` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `タイトル` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `内容` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `投稿者種別` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `添付画像_任意_` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `成果物ファイルパス` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `提出日時` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `deliverables`
--
ALTER TABLE `deliverables`
  ADD PRIMARY KEY (`成果ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
