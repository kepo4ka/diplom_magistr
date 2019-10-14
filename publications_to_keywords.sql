-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Окт 15 2019 г., 01:10
-- Версия сервера: 10.1.16-MariaDB
-- Версия PHP: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `elibrary`
--

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_keywords`
--

CREATE TABLE `publications_to_keywords` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `keywordid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `publications_to_keywords`
--

INSERT INTO `publications_to_keywords` (`id`, `publicationid`, `keywordid`) VALUES
(1, 39150448, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `publications_to_keywords`
--
ALTER TABLE `publications_to_keywords`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keywordid` (`keywordid`,`publicationid`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `publications_to_keywords`
--
ALTER TABLE `publications_to_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
