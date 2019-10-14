-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Окт 15 2019 г., 01:24
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
-- Структура таблицы `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `fio` varchar(255) NOT NULL,
  `post` text NOT NULL,
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
  `authorid` int(11) NOT NULL,
  `orgsid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `keywords`
--

CREATE TABLE `keywords` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `mylog`
--

CREATE TABLE `mylog` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `value` text NOT NULL,
  `dop` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `organisations`
--

CREATE TABLE `organisations` (
  `id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_en` varchar(500) NOT NULL,
  `type` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `organisations`
--

INSERT INTO `organisations` (`id`, `name`, `name_en`, `type`, `city`, `country`, `region`) VALUES
(5051, 'Федеральное государственное бюджетное образовательное учреждение высшего профессионального образования "Северо-Осетинский государственный университет имени К.Л. Хетагурова"', 'North Ossetian State University', 'Высшее учебное заведение', 'Владикавказ', 'Россия', 'Республика Северная Осетия - Алания');

-- --------------------------------------------------------

--
-- Структура таблицы `publications`
--

CREATE TABLE `publications` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `type` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `language` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `publications`
--

INSERT INTO `publications` (`id`, `title`, `type`, `year`, `language`) VALUES
(37039312, 'ИСТОРИЯ ГОСУДАРСТВА И ПРАВА ЗАРУБЕЖНЫХ СТРАН', 'учебное пособие', 2019, 'русский');

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_authors`
--

CREATE TABLE `publications_to_authors` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `authorid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `publications_to_authors`
--

INSERT INTO `publications_to_authors` (`id`, `publicationid`, `authorid`) VALUES
(1619, 37039312, 772375),
(1618, 37039312, 781679),
(1620, 37039312, 871974);

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_keywords`
--

CREATE TABLE `publications_to_keywords` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `keywordid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `publications_to_organisations`
--

CREATE TABLE `publications_to_organisations` (
  `id` int(11) NOT NULL,
  `publicationid` int(11) NOT NULL,
  `orgsid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `publications_to_organisations`
--

INSERT INTO `publications_to_organisations` (`id`, `publicationid`, `orgsid`) VALUES
(3621, 37039312, 5051);

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
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `authors_to_organisations`
--
ALTER TABLE `authors_to_organisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orgsid` (`authorid`,`orgsid`),
  ADD KEY `orgsid_2` (`orgsid`);

--
-- Индексы таблицы `keywords`
--
ALTER TABLE `keywords`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `mylog`
--
ALTER TABLE `mylog`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `organisations`
--
ALTER TABLE `organisations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `city` (`city`),
  ADD KEY `country` (`country`),
  ADD KEY `region` (`region`),
  ADD KEY `name` (`name`(255)),
  ADD KEY `name_en` (`name_en`(255));

--
-- Индексы таблицы `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `publications_to_authors`
--
ALTER TABLE `publications_to_authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `authorid_2` (`authorid`,`publicationid`),
  ADD KEY `publicationid` (`publicationid`),
  ADD KEY `authorid` (`authorid`);

--
-- Индексы таблицы `publications_to_keywords`
--
ALTER TABLE `publications_to_keywords`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keywordid` (`keywordid`,`publicationid`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1044155;
--
-- AUTO_INCREMENT для таблицы `authors_to_organisations`
--
ALTER TABLE `authors_to_organisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=745;
--
-- AUTO_INCREMENT для таблицы `mylog`
--
ALTER TABLE `mylog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `publications_to_authors`
--
ALTER TABLE `publications_to_authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1621;
--
-- AUTO_INCREMENT для таблицы `publications_to_keywords`
--
ALTER TABLE `publications_to_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `publications_to_organisations`
--
ALTER TABLE `publications_to_organisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3622;
--
-- AUTO_INCREMENT для таблицы `publications_to_publications`
--
ALTER TABLE `publications_to_publications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
