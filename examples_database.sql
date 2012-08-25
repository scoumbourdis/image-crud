-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 25, 2012 at 03:11 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `examples_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `example_1`
--

CREATE TABLE IF NOT EXISTS `example_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=178 ;

--
-- Dumping data for table `example_1`
--

INSERT INTO `example_1` (`id`, `url`) VALUES
(172, 'c360-9.jpg'),
(176, 'b7b8-18.jpg'),
(177, '3fd6-21.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `example_2`
--

CREATE TABLE IF NOT EXISTS `example_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(250) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=182 ;

--
-- Dumping data for table `example_2`
--

INSERT INTO `example_2` (`id`, `url`, `priority`) VALUES
(172, 'eb4f-51.jpg', NULL),
(173, 'ac84-52.jpg', NULL),
(176, '7ad8-63.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `example_3`
--

CREATE TABLE IF NOT EXISTS `example_3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(250) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=180 ;

--
-- Dumping data for table `example_3`
--

INSERT INTO `example_3` (`id`, `url`, `category_id`, `priority`) VALUES
(172, 'a48d-88.jpg', 22, NULL),
(173, '5e32-89.jpg', 22, NULL),
(174, '7628-90.jpg', 22, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `example_4`
--

CREATE TABLE IF NOT EXISTS `example_4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `url` varchar(250) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=182 ;

--
-- Dumping data for table `example_4`
--

INSERT INTO `example_4` (`id`, `title`, `url`, `priority`) VALUES
(172, 'My house!', 'eb4f-51.jpg', 1),
(173, 'Some flowers', 'ac84-52.jpg', 3),
(176, 'My garden!', '7ad8-63.jpg', 2);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
