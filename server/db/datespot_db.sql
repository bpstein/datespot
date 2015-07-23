-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 23, 2015 at 12:01 AM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

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
  `venue_created` datetime DEFAULT NULL,
  `venue_modified` datetime DEFAULT NULL,
  `venue_unique_id` char(32) NOT NULL,
  `venue_is_event_flag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Is this entry in the venue table... actually an event?',
  `venue_is_event_at_venue_id` int(10) unsigned DEFAULT NULL COMMENT 'Is this event at an existing venue?',
  `venue_name` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_description` text CHARACTER SET ascii NOT NULL,
  `venue_country` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'UK',
  `venue_city` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'London',
  `venue_postcode` varchar(32) CHARACTER SET ascii NOT NULL,
  `venue_address` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_location_lat` decimal(9,6) DEFAULT NULL COMMENT 'Latitude as a Decimal. In geography, latitude (φ) is a geographic coordinate that specifies the north-south position of a point on the Earth''s surface. Latitude is an angle (defined below) which ranges from 0° at the Equator to 90° (North or South) at the poles.',
  `venue_location_lon` decimal(9,6) DEFAULT NULL COMMENT 'Longitude as a Decimal. Longitude (/ˈlɒndʒɨtjuːd/ or /ˈlɒndʒɨtuːd/, British also /ˈlɒŋɡɨtjuːd/),[1] is a geographic coordinate that specifies the east-west position of a point on the Earth''s surface.',
  `venue_location_spatial_point` point DEFAULT NULL COMMENT 'MySQL Geometric Point',
  `venue_rating_general` tinyint(3) unsigned DEFAULT NULL COMMENT 'DateSpot Rating',
  `venue_rating_cost` tinyint(3) unsigned DEFAULT NULL COMMENT 'The higher the rating the higher the cost',
  `venue_rating_quirkiness` tinyint(3) unsigned DEFAULT NULL,
  `venue_scenario` set('firstdate','seconddate','drinksonly','dinnerdate','thirddate','sealthedeal','goallout','activedate','visitor','friends','brunch') NOT NULL COMMENT 'The scenario',
  `venue_url` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `venue_open_sunday` char(1) NOT NULL,
  `venue_open_monday` char(1) NOT NULL,
  `venue_hour_open` time DEFAULT NULL,
  `venue_hour_close` time DEFAULT NULL,
  `venue_hour_monday_open` time NOT NULL,
  `venue_hour_monday_close` time DEFAULT NULL,
  `venue_hour_tuesday_open` time DEFAULT NULL,
  `venue_hour_tuesday_close` time DEFAULT NULL,
  `venue_hour_wednesday_open` time DEFAULT NULL,
  `venue_hour_wednesday_close` time DEFAULT NULL,
  `venue_hour_thursday_open` time DEFAULT NULL,
  `venue_hour_thursday_close` time DEFAULT NULL,
  `venue_hour_friday_open` time DEFAULT NULL,
  `venue_hour_friday_close` time DEFAULT NULL,
  `venue_hour_saturday_open` time DEFAULT NULL,
  `venue_hour_saturday_close` time DEFAULT NULL,
  `venue_hour_sunday_open` time DEFAULT NULL,
  `venue_hour_sunday_close` time DEFAULT NULL,
  `venue_date_open` date DEFAULT NULL COMMENT 'The permanent opening date of the venue, for example, it might simply be pop-up that is only open for a particular date range',
  `venue_date_close` date DEFAULT NULL COMMENT 'The permanent closing date of the venue, for example, it might simply be pop-up that is only open for a particular date range',
  `venue_date_operating_frequency_1` varchar(255) DEFAULT NULL COMMENT 'The operating frequency if not every day during the operating period, according to PHP relative date format',
  `venue_date_operating_frequency_1_next_calculated` date DEFAULT NULL COMMENT 'The next calculated operating frequency.',
  `venue_date_operating_frequency_2` varchar(266) DEFAULT NULL COMMENT 'The operating frequency if not every day during the operating period, according to PHP relative date format',
  `venue_date_operating_frequency_2_next_calculated` date DEFAULT NULL COMMENT 'The next calculated operating frequency.',
  PRIMARY KEY (`venue_id`),
  KEY `venue_location_spatial_point` (`venue_location_spatial_point`(25))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='The datespot venues. The proprietary information is here.' AUTO_INCREMENT=6 ;

--
-- Dumping data for table `ds_venue`
--

INSERT INTO `ds_venue` (`venue_id`, `venue_created`, `venue_modified`, `venue_unique_id`, `venue_is_event_flag`, `venue_is_event_at_venue_id`, `venue_name`, `venue_description`, `venue_country`, `venue_city`, `venue_postcode`, `venue_address`, `venue_location_lat`, `venue_location_lon`, `venue_location_spatial_point`, `venue_rating_general`, `venue_rating_cost`, `venue_rating_quirkiness`, `venue_scenario`, `venue_url`, `venue_open_sunday`, `venue_open_monday`, `venue_hour_open`, `venue_hour_close`, `venue_hour_monday_open`, `venue_hour_monday_close`, `venue_hour_tuesday_open`, `venue_hour_tuesday_close`, `venue_hour_wednesday_open`, `venue_hour_wednesday_close`, `venue_hour_thursday_open`, `venue_hour_thursday_close`, `venue_hour_friday_open`, `venue_hour_friday_close`, `venue_hour_saturday_open`, `venue_hour_saturday_close`, `venue_hour_sunday_open`, `venue_hour_sunday_close`, `venue_date_open`, `venue_date_close`, `venue_date_operating_frequency_1`, `venue_date_operating_frequency_1_next_calculated`, `venue_date_operating_frequency_2`, `venue_date_operating_frequency_2_next_calculated`) VALUES
(1, '2015-05-30 21:05:00', '2015-07-15 23:38:27', 'fb92141e748483fabc5037e46b69e854', 'N', NULL, 'The Northcote', 'A nice pup', 'UK', 'London', 'This is the postcode', 'This is the address', 51.460677, -0.167190, '\0\0\0\0\0\0\0{��v��I@J�_{fſ', 1, 3, 2, 'firstdate,drinksonly,dinnerdate,sealthedeal,activedate,visitor,friends', 'This is the URL', 'Y', 'Y', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '19:00:00', '21:00:00', '2015-07-16', '2020-11-04', '', NULL, NULL, NULL),
(2, '2015-05-30 21:05:10', '2015-07-16 00:53:20', '3543edeb9428eaa57292059980a858fd', 'N', NULL, 'The Slug at Fulham', 'Where you go on a saturday night to get your shoes destroyed and pick up... something.', 'UK', 'London', 'SW6 5NH', '490 Fulham RoadFulham, London SW6 5NH', 51.480316, -0.198339, '\0\0\0\0\0\0\0nߣ�z�I@f�B,cɿ', 2, 1, 0, 'drinksonly', '', 'Y', 'Y', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '0000-11-30', '0000-11-30', '', NULL, NULL, NULL),
(4, '2015-05-31 16:28:30', '2015-07-12 18:35:08', 'cefd9cd928cd76538025bf532767a988', 'N', NULL, 'Test Venue', 'This is a test venue', 'UK', 'London', 'SW66SR', '40 Kingwood Road', NULL, NULL, NULL, 6, 0, 0, 'firstdate', 'http://faptastic.com', 'Y', 'Y', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '2015-06-03', '2015-06-22', NULL, NULL, NULL, NULL),
(5, '2015-07-12 20:03:59', '2015-07-16 00:58:02', 'd84f5648b4cb83372c6c4c4b9733af57', 'N', NULL, 'Test Venue2', 'Description', 'UK', 'London', 'POSTCODE', 'Address', 51.462416, -0.168803, '\0\0\0\0\0\0\0Ւ�r0�I@o�;2V�ſ', 10, 5, 1, 'firstdate,friends,brunch', 'http://fap123.com', 'N', 'N', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '0000-11-30', '0000-11-30', 'first monday of next month', '2015-08-03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ds_venue_image`
--

CREATE TABLE IF NOT EXISTS `ds_venue_image` (
  `venue_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_image_unique_id` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `venue_id` int(10) unsigned NOT NULL,
  `venue_image_order` tinyint(3) unsigned NOT NULL,
  `venue_image_data` mediumblob NOT NULL,
  `venue_image_thumbnail_data` mediumblob NOT NULL,
  `venue_image_data_format` varchar(64) NOT NULL,
  `venue_image_hash` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `venue_image_description` tinytext NOT NULL COMMENT 'A description of what the photo of the venue is showing',
  PRIMARY KEY (`venue_image_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Photos or images to be uploaded for a particular venue' AUTO_INCREMENT=6 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
