-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 12 Wrz 2013, 19:56
-- Wersja serwera: 5.1.70-log
-- Wersja PHP: 5.4.17-pl0-gentoo

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `test_panthera`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `{$db_prefix}private_messages`
--

CREATE TABLE IF NOT EXISTS `{$db_prefix}private_messages` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `sender` varchar(32) NOT NULL,
  `sender_id` int(6) NOT NULL,
  `recipient` varchar(32) NOT NULL,
  `recipient_id` int(6) NOT NULL,
  `content` varchar(2048) NOT NULL,
  `directory` varchar(32) NOT NULL DEFAULT 'inbox' COMMENT 'directory/folder where message will be shown (default: inbox)',
  `sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `visibility_sender` tinyint(1) NOT NULL,
  `visibility_recipient` tinyint(1) NOT NULL,
  `seen` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
