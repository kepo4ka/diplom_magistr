-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Сен 27 2019 г., 16:39
-- Версия сервера: 10.1.34-MariaDB
-- Версия PHP: 5.6.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
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
-- Структура таблицы `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `authorid` varchar(32) NOT NULL,
  `fio` int(11) NOT NULL,
  `articles_count` int(11) NOT NULL,
  `citation_count` int(11) NOT NULL,
  `hirsch_index` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `authors_to_organisations`
--

CREATE TABLE `authors_to_organisations` (
  `id` int(11) NOT NULL,
  `orgsid` int(11) NOT NULL,
  `authorid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `organisations`
--

CREATE TABLE `organisations` (
  `orgsid` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_en` varchar(500) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `publications`
--

CREATE TABLE `publications` (
  `publicationid` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_authors`
--

CREATE TABLE `publications_to_authors` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `authorid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_organisations`
--

CREATE TABLE `publications_to_organisations` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `orgsid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_publications`
--

CREATE TABLE `publications_to_publications` (
  `id` int(11) NOT NULL,
  `origin_publ_id` int(11) NOT NULL,
  `end_publ_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `authorid` (`authorid`);

--
-- Индексы таблицы `authors_to_organisations`
--
ALTER TABLE `authors_to_organisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orgsid` (`orgsid`,`authorid`),
  ADD KEY `authorid` (`authorid`);

--
-- Индексы таблицы `organisations`
--
ALTER TABLE `organisations`
  ADD PRIMARY KEY (`orgsid`);

--
-- Индексы таблицы `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`publicationid`);

--
-- Индексы таблицы `publications_to_authors`
--
ALTER TABLE `publications_to_authors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authorid` (`authorid`),
  ADD KEY `publicationid` (`publicationid`);

--
-- Индексы таблицы `publications_to_organisations`
--
ALTER TABLE `publications_to_organisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `publicationid` (`publicationid`,`orgsid`),
  ADD KEY `orgsid` (`orgsid`);

--
-- Индексы таблицы `publications_to_publications`
--
ALTER TABLE `publications_to_publications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `origin_publ_id` (`origin_publ_id`,`end_publ_id`),
  ADD KEY `end_publ_id` (`end_publ_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `authors_to_organisations`
--
ALTER TABLE `authors_to_organisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `publications_to_authors`
--
ALTER TABLE `publications_to_authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `publications_to_organisations`
--
ALTER TABLE `publications_to_organisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `publications_to_publications`
--
ALTER TABLE `publications_to_publications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
