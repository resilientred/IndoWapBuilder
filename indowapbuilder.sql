-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 10 Nov 2014 pada 14.52
-- Versi Server: 5.5.36
-- PHP Version: 5.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `indowapbuilder`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `set`
--

DROP TABLE IF EXISTS `set`;
CREATE TABLE `set` (
  `key` varchar(32) NOT NULL,
  `val` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `set`
--

INSERT INTO `set` (`key`, `val`) VALUES
('siteurl', 'http://localhost.com'),
('sitename', 'IndoWapBuilder'),
('theme', 'default'),
('domains', 'a:2:{i:0;s:10:"your.my.id";i:1;s:15:"indowapblog.com";}'),
('maxsites', '10'),
('pageview', '10'),
('timezone', '7'),
('filesize', '10000'),
('siteemail', 'admin@domain.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site`
--

DROP TABLE IF EXISTS `site`;
CREATE TABLE `site` (
  `site_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `url` varchar(100) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`site_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `rights` tinyint(2) NOT NULL DEFAULT '0',
  `gender` enum('male','female','') NOT NULL DEFAULT '',
  `about` varchar(500) NOT NULL DEFAULT '',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(64) NOT NULL DEFAULT '',
  `regtime` int(10) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `u_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
