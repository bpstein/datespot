-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 21, 2015 at 11:05 PM
-- Server version: 5.5.41-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `datespot_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ds_user`
--

CREATE TABLE IF NOT EXISTS `ds_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_unique_id` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_first_name` varchar(255) CHARACTER SET utf16 NOT NULL,
  `user_last_name` varchar(255) CHARACTER SET utf16 NOT NULL,
  `user_last_login` datetime NOT NULL,
  `user_login_count` int(11) NOT NULL,
  `user_gender` char(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='The Datespot User Data' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ds_user_event_log`
--

CREATE TABLE IF NOT EXISTS `ds_user_event_log` (
  `user_id` int(11) NOT NULL,
  `user_event_type` int(11) NOT NULL,
  `user_event_datetime` int(11) NOT NULL,
  `user_event_location` decimal(9,6) DEFAULT NULL,
  `user_event_description` varchar(255) NOT NULL,
  `user_event_session_id` int(10) unsigned DEFAULT NULL,
  `user_event_data` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ds_user_location`
--

CREATE TABLE IF NOT EXISTS `ds_user_location` (
  `user_id` int(10) unsigned NOT NULL,
  `user_location_date` datetime NOT NULL,
  `user_location_lat` decimal(9,6) NOT NULL,
  `user_location_long` decimal(9,6) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User Location';

-- --------------------------------------------------------

--
-- Table structure for table `ds_user_session`
--

CREATE TABLE IF NOT EXISTS `ds_user_session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_session_hash` varchar(32) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ds_user_session_venue_selection`
--

CREATE TABLE IF NOT EXISTS `ds_user_session_venue_selection` (
  `session_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `venue_id` int(10) unsigned NOT NULL,
  `venue_chosen` char(1) NOT NULL,
  `venue_ranking` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='What a person has chosen for a particular session (if available)';

-- --------------------------------------------------------

--
-- Table structure for table `ds_venue`
--

CREATE TABLE IF NOT EXISTS `ds_venue` (
  `venue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_unique_id` varchar(255) NOT NULL,
  `venue_name` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_description` text CHARACTER SET ascii NOT NULL,
  `venue_country` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'UK',
  `venue_city` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'London',
  `venue_postcode` varchar(32) CHARACTER SET ascii NOT NULL,
  `venue_address` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_rating_general` tinyint(3) unsigned NOT NULL COMMENT 'DateSpot Rating',
  `venue_rating_cost` tinyint(3) unsigned NOT NULL COMMENT 'The higher the rating the higher the cost',
  `venue_scenario` set('firstdate','seconddate','drinksonly','dinnerdate','thirddate','sealthedeal','goallout') NOT NULL COMMENT 'The scenario',
  `venue_image_1` blob NOT NULL,
  `venue_image_2` blob NOT NULL,
  `venue_image_3` blob NOT NULL,
  `venue_image_4` blob NOT NULL,
  `venue_url` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `venue_open_sunday` char(1) NOT NULL,
  `venue_open_monday` char(1) NOT NULL,
  `venue_hour_open` time DEFAULT NULL,
  `venue_hour_close` time DEFAULT NULL,
  PRIMARY KEY (`venue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='The datespot venues. The proprietary information is here.' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
