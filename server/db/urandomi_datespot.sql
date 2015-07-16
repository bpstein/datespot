-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 13, 2015 at 08:04 AM
-- Server version: 5.6.23
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `urandomi_datespot`
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
  `venue_unique_id` varchar(255) NOT NULL,
  `venue_name` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_description` text CHARACTER SET ascii NOT NULL,
  `venue_country` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'UK',
  `venue_city` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT 'London',
  `venue_postcode` varchar(32) CHARACTER SET ascii NOT NULL,
  `venue_address` varchar(255) CHARACTER SET ascii NOT NULL,
  `venue_location_lat` decimal(9,6) DEFAULT NULL COMMENT 'Latitude as a Decimal',
  `venue_location_lon` decimal(9,6) DEFAULT NULL COMMENT 'Longitude as a Decimal',
  `venue_location_spatial_point` point DEFAULT NULL COMMENT 'MySQL Geometric Point',
  `venue_rating_general` tinyint(3) unsigned DEFAULT NULL COMMENT 'DateSpot Rating',
  `venue_rating_cost` tinyint(3) unsigned DEFAULT NULL COMMENT 'The higher the rating the higher the cost',
  `venue_rating_quirkiness` tinyint(3) unsigned DEFAULT NULL,
  `venue_scenario` set('firstdate','seconddate','drinksonly','dinnerdate','thirddate','sealthedeal','goallout','activedate','visitor','friends','brunch') NOT NULL COMMENT 'The scenario',
  `venue_image_1` blob NOT NULL,
  `venue_image_2` blob NOT NULL,
  `venue_image_3` blob NOT NULL,
  `venue_image_4` blob NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='The datespot venues. The proprietary information is here.' AUTO_INCREMENT=20 ;

--
-- Dumping data for table `ds_venue`
--

INSERT INTO `ds_venue` (`venue_id`, `venue_created`, `venue_modified`, `venue_unique_id`, `venue_name`, `venue_description`, `venue_country`, `venue_city`, `venue_postcode`, `venue_address`, `venue_location_lat`, `venue_location_lon`, `venue_location_spatial_point`, `venue_rating_general`, `venue_rating_cost`, `venue_rating_quirkiness`, `venue_scenario`, `venue_image_1`, `venue_image_2`, `venue_image_3`, `venue_image_4`, `venue_url`, `venue_open_sunday`, `venue_open_monday`, `venue_hour_open`, `venue_hour_close`, `venue_hour_monday_open`, `venue_hour_monday_close`, `venue_hour_tuesday_open`, `venue_hour_tuesday_close`, `venue_hour_wednesday_open`, `venue_hour_wednesday_close`, `venue_hour_thursday_open`, `venue_hour_thursday_close`, `venue_hour_friday_open`, `venue_hour_friday_close`, `venue_hour_saturday_open`, `venue_hour_saturday_close`, `venue_hour_sunday_open`, `venue_hour_sunday_close`, `venue_date_open`, `venue_date_close`, `venue_date_operating_frequency_1`, `venue_date_operating_frequency_1_next_calculated`, `venue_date_operating_frequency_2`, `venue_date_operating_frequency_2_next_calculated`) VALUES
(1, '2015-05-30 21:05:00', '2015-05-31 16:29:04', 'fb92141e748483fabc5037e46b69e854', 'The Northcote', 'Quirky pub with rustic wooden tables, chalkboard menus, TV sport and album covers on the walls. ', 'UK', 'London', 'SW11 1N', 'The Northcote2 Northcote RoadLondon SW11 1N', NULL, NULL, NULL, 5, 3, 0, 'firstdate,drinksonly', '', '', '', '', 'http://faptastic', 'Y', 'Y', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', '00:00:00', '08:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(2, '2015-05-30 21:05:10', '2015-05-30 21:05:10', '3543edeb9428eaa57292059980a858fd', 'The Slug at Fulham', 'Where you go on a saturday night to get your shoes destroyed and pick up... something.', 'UK', 'London', 'SW6 5NH', '490 Fulham Road\r\nFulham, London SW6 5NH', NULL, NULL, NULL, 2, 1, 0, '', '', '', '', '', '', 'Y', 'Y', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', '12:00:00', '02:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(4, '2015-05-31 16:28:30', '2015-05-31 16:28:30', 'cefd9cd928cd76538025bf532767a988', 'Test Venue', 'This is a test venue', 'UK', 'London', 'SW66SR', '40 Kingwood Road', NULL, NULL, NULL, 6, 0, 0, 'firstdate,seconddate', '', '', '', '', 'http://faptastic.com', 'Y', 'Y', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', '09:00:00', '05:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(5, '2015-06-04 19:31:16', '2015-06-04 19:32:04', 'cb02ee8090a30023bcb0a42ccaed2c4a', 'Love Die Late', 'Love Die Late is a fusion of coffee specialist and cocktail parlour, turning the urban barista on its head to introduce a new drinking trend to a tucked away street in Fitzrovia.By day, Love Die Late satisfies your caffeine cravings and daytime jitters with the finest bean blends and espresso servings. Arrive at night to find a different scenario entirely, as this kooky cafe transforms into a late night emporium of crafty mixology and luscious libations. The ground floor is a hive of activity, with groups of friends and couples settling at tables and bar stools, and theres a cosy, more chilledout space to discover in the basement.Take to this chameleon of a Fitrovia venue for a cheeky coffee followed up by a cocktail or two.', 'UK', 'London', 'W1W 6PH', '114 Great Portland Street', NULL, NULL, NULL, 8, 5, 0, 'firstdate,drinksonly', '', '', '', '', 'http://www.lovedielate.com/', 'Y', 'Y', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', '07:30:00', '23:30:00', NULL, NULL, NULL, NULL, NULL, NULL),
(6, '2015-06-04 19:42:13', '2015-06-04 19:43:19', '99a3a8d216a97b668d75a0b77071ab7d', 'Oskars Bar At Dabbous', 'Situated on Sohos Whitfield Street is the fantastic basement cocktail bar owned by Ollie Dabbous and Oskar Kinberg. Set below the famous Michelinstarred restaurant  Dabbous  the bar is run by the award winning mixologist Oskar Kinberg, which explains why the cocktails are of the highest quality. The speakeasy style bar setting, complete with exposed brickwork, warehouse style furniture and moody lights hanging above the bar, is the perfect backdrop for a catch up with friends. The cocktails served are expertly crafted concoctions and are matched perfectly with the selection of bar snacks including, olives, chicken wings and steak sandwiches The intimate, classy setting is ideal for impressing a date or celebrating a special occasion and with a late night DJ on weekends, its a great place to start your night out.', 'UK', 'London', 'W1T 2SF', '39 Whitfield Street, Fitzrovia', NULL, NULL, NULL, 9, 7, 0, 'firstdate,drinksonly', '', '', '', '', 'http://www.dabbous.co.uk/', 'N', 'N', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', '17:30:00', '23:30:00', NULL, NULL, NULL, NULL, NULL, NULL),
(7, '2015-06-04 19:50:04', '2015-06-25 00:39:27', '4e66817a9fd959370249844edef3d550', 'Shaker and Company', 'ShakerCompany is a neighbourhood cocktail bar and kitchen just around the corner from Euston station. The venue has constructed quite a name for itself with interesting and inspired cocktails as well as delicious homemade pizzas and sharing boards. Shaker and Company has an essence of the New Orleans scene with exposed brickwork, stags on the wall, minimal hanging light bulbs and bourbon street fans. Wonder downstairs toward BelleCo a bohemian drinking den set in the Belle poque era. Belle is a hidden snug established below the bustling main bar at Shaker  Company. It has original Victorian floorboards, goldtrimmed burgundy theatre curtains and gold leaf mulberry damask walls. Down there they host Tuesday night Open Mic Comedy nights, as well as various events throughout the year and it is also available for private party hire. Shaker and Company, gives you every reason to venture up to Warren Street and Euston to check out a London cocktail bar perfect for chilling with your mates or impressing a first time date. Book a table at this hidden gem now. ', 'UK', 'London', 'NW1 3EE', '119 Hampstead Road', NULL, NULL, NULL, 6, 4, 0, 'firstdate,drinksonly', '', '', '', '', 'http://www.shakerandcompany.co.uk/', 'Y', 'Y', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '0000-11-30', '0000-11-30', NULL, NULL, NULL, NULL),
(8, '2015-06-04 19:58:57', '2015-06-04 20:07:39', '8b911dcca6ac70c47ec62cf0ba923fb6', 'Craft Cocktail Co', 'A unique cocktail establishment in the heart of Bethnal Green, Craft Cocktail Co. are here to serve some of the finest concoctions to be found in the East End. From the thriving JJ Goodman noggin, comes a bar that spreads their passion for cocktails in a whole new way.Self described as a craft cocktail factory, expect a range of innovative and beautifully crafted drinks to satisfy even the biggest cocktail fanatic. With an industrial vibe, the bar has two distinct parts. On the first floor mezzanine, you can sit back and watch the hum of the bottling machines, as Craft Cocktail Co. dishes out bottled concoctions for restaurants, pubs and private events  effectively taking AGrade cocktails to the masses. Downstairs in the bar you can then enjoy all these wonderful libations by the glass  so you wont miss out on any of the funIdeal for the real cocktail lovers amongst you, this East London gem, nestled in Bethnal Greens arches, is a real treat.', 'UK', 'London', 'E2 9LE', 'Arch 253 Paradise Row', NULL, NULL, NULL, 5, 4, 0, 'firstdate,drinksonly', '', '', '', '', 'http://craftcocktailcompany.com/', 'N', 'N', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', '16:00:00', '23:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(9, '2015-06-04 20:13:12', '2015-06-04 20:13:12', '3cc7e6c49936d06d37279c4299f1afec', 'Trapeze', 'Just a couple minutes walk away from the beating heart of Londons hip young scene in Shoreditch, Trapeze is as quirky as they come.Like the trophy room of a retired ringmaster, it is decorated in the faded wares of a onceglorious circus youll sit beneath hanging trapeze bars in carousel booths, admiring the clown costume framed behind you. Or, if youre feeling lively you can venture down into the basement club to throw shapes with some more brightly coloured souls at one of their many retro, house or alternative nights.The food and drinks at Trapeze never fail to disappoint in their visual impressiveness either a showstopper cocktail is perfect for bonding over giggles with friends at the extraordinarily eclectic drinking vessel it happens to come in. If you prefer a more private drinking experience and want a cocktail for one, their menu has variations of all of the classics  with suitably amusing punny names of course. Alongside your Beard Of Bees cocktail, you might want to sample some of Trapezes tasty burgers, sliders and BBQ plates. When you see what the table next door have ordered you wont really have a choice. This bar and downstairs club is perfect for hanging out with friends in intimate booths, or for impressing a new date with your knowledge of quirky London hangouts. ', 'UK', 'London', 'EC2A 3HX ', '89 Great Eastern Street, Shoreditch', NULL, NULL, NULL, 7, 5, 0, 'firstdate,seconddate,drinksonly,dinnerdate', '', '', '', '', 'http://www.trapezebar.com/', 'N', 'N', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(10, '2015-06-04 20:50:42', '2015-06-04 20:50:42', 'd1fd0e557b010d310e0e7ebb51bdd79c', 'Trailer Happiness', 'Trailer Happiness is an intimate lounge bar, den and kitchen on Portobello Road with the ezboy feel of a low rent, mid60s California valley bachelor pad.Set in a basement on Portobello Road, this is one of the very fine cocktail bars in West London and certainly Notting Hill. The decor is quirky, offbeat and buzzes with young Londoners looking to party and sample of the best cocktails around. Its a cosmopolitan kitsch Tiki Bar in London, something a little different and a fun night in London. Booking recommended. Ideal for all cocktail aficionados, their team of mixologists will amaze even the most diehard of cocktail fans with their incredibly innovative mixes. Also hosting regular DJ nights, its a really fun party place that you wont want to miss out from. ', 'UK', 'London', 'W11 2DY', '177 Portobello Road', NULL, NULL, NULL, 7, 6, 0, 'drinksonly', '', '', '', '', 'http://www.trailerhappiness.com/', 'N', 'N', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', NULL, NULL, NULL, NULL, NULL, NULL),
(11, '2015-06-04 20:59:20', '2015-06-04 20:59:20', '57b9bef792f39a2baca7fd78a6eaf9df', 'The Chelsea Prayer Room ', 'NOTE: ONLY OPEN THURSDAY, FRIDAY, SATURDAY NIGHTS.The Chelsea Prayer Room, located upstairs of one of Londons coolest restaurant bars  Goat DINE, in a secret bar with a stylish speakeasy vibe.The decoration throughout pays homage to George Moorland, the famous artist who drank in the Goat and Boots and painted the original sign for the historic pub in the 1700s in lieu of his bar bill, so expect to be surrounded by chesterfields chairs, vintage shakers and cocktail books. Technically you have to be a member to enter this secret cocktail haven, but book your spot with DesignMyNight, get the all important code and enter it on the keypad next to the door to gain entry.The special cocktail list at The Chelsea Prayer Room will be slightly different from the main bar Goat DRINK, with unbranded bottles providing an element of mystery to your drinking experience. Guests can enjoy Goat DINEs food menu and then head upstairs for a more relaxed and chilled drink. With an intimate capacity of only 30 people and the sweet sound of Old School Jazz, The Chelsea Prayer Room is a drinking and cocktail experience like no other.', 'UK', 'London', 'SW10 9QL', '333 Fulham Road', NULL, NULL, NULL, 8, 7, 0, 'firstdate,seconddate,drinksonly', '', '', '', '', 'http://www.goatchelsea.com/secret/', 'N', 'N', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', '20:00:00', '01:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(12, '2015-06-04 21:11:14', '2015-06-04 21:29:35', '101817f789facd7b45cf37b8a4b57c4b', 'The Fellow', 'The Fellow is a discreetly stylish gastronomic wonderland of a pub just around the corner from the grandeur of the new Kings Cross station. With a focus on the finer things, smashing cocktails and some wonderful spaces to enjoy them in, The Fellow is the ideal place to gather your gang together to celebrate an engagement, a birthday or a Friday', 'UK', 'London', 'N1 9AA', '24 York Way', NULL, NULL, NULL, 4, 4, 0, 'seconddate,dinnerdate', '', '', '', '', 'http://www.geronimoinns.co.uk/londonthefellow/', 'Y', 'Y', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(14, '2015-06-08 21:03:23', '2015-06-25 00:41:09', 'ce5ca867717380aeb2fc3ff755e558f5', 'Oaka at The Mansion House ', 'Oaka at The Mansion House is a pub and dining room in the heart of Kennington, London. Just minutes from Kennington tube station, this sleek and stylish venue offers a unique combination of Pan Asian cuisine accompanied with hand crafted real ales by acclaimed Peterboroughbased brewery Oakham Ales.The award winning brews include JHB, Citra, Inferno, Bishops Farewell and Green Devil, each one as lipsmacking as the next but a perfect match to the Pan Asian cuisine. The food served is a great selection from SouthEast Asia, including dishes from Thailand, Japan, Korea, Vietnam, Malaysia, Singapore, Indonesia, Laos and China. Tuck into amazing dishes that include aromatic duck and pancakes, honeyed spare ribs, chicken rendang and Pad Thai. A great place to stop for lunch and dinner, this South London gem is well worth a visit.', 'UK', 'London', 'SE11 4RS', '48 Kennington Park Road', NULL, NULL, NULL, 5, 4, 2, 'firstdate,drinksonly,dinnerdate', '', '', '', '', 'http://www.oakalondon.com/', 'Y', 'Y', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '00:30:00', '0000-11-30', '0000-11-30', NULL, NULL, NULL, NULL),
(13, '2015-06-04 21:12:20', '2015-06-04 21:12:20', '9eea459e1b672dbd0c36a10991acf319', 'Lost and Found', 'Sassy on down to Balham this evening to indulge in a little homespun, cocktail whimsy as Lost and Found caters to all your liquid needs.More like a living room than a lounge bar, Lost and Found are keeping the cool of kitsch alive with their homage to all things homespun. From ditsy, floral wallpapers and gilded photo frames to vintage odds and sods, this venue has quirky, off kilter charm throughout. Set across two floors, guests at Lost and Found can tuck into cocktails upstairs, or broody beats down in the basement. Dive in to a pool of cocktail creativity as Lost and Found whip up some truly loveable concoctions. Whether youre laying in to the Candy Bar at 8 with its Frangelico Hazelnut, butterscotch and Kahlua, or keeping things punchy with a Tequila, cinnamon and chilli infused El Mariachi, Lost and Found are keeping the art of mixology alive with their blend of creative numbers and classics. Sip, sup and suffer the brilliant consequences.', 'UK', 'London', 'SW12 9RG', '10 Bedford Hill, Balham', NULL, NULL, NULL, 6, 3, 0, 'firstdate,seconddate,drinksonly', '', '', '', '', 'http://www.lostandfoundbar.co.uk/', 'Y', 'Y', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(15, '2015-06-18 00:55:23', '2015-06-18 00:55:23', 'b380f83946c019837234f6bd4f77753c', 'Roka Mayfair', 'located on north audley street, moments from the bustling shopping hub of oxford street, ROKA brings its unique style of contemporary japanese robatayaki cuisine and its sophisticated design to this exclusive london neighbourhood.the heart of the restaurant is the robata grill, which is an integral design element of the room and source of the vibrant, welcoming energy that flows throughout. contemporary in feel, materials and textures are gracefully balanced reclaimed and natural wood, raw steel and casted concrete.  In addition, glimpses of crimson complement the design with a plush upholstered wall, handpainted with japanese prints.on the weekends ROKA mayfair is open for dining throughout the day.Oh my what is this restaurant good HeavenlyThe best. I am in love. I had trouble getting a reservation here so I would recommend making a reservation a few weeks before especially if you want to go in the weekend. I also recommend you try the Premium tasting menu.. Expensive but the best, and it allows you to try things you may not select yourself. I basically could not speak a few times because I was just trying to figure out how to express myself about the divine food that entered my mouth hahaOkay so the restaurant is located in Mayfair there is another Roka in Marlybone  see Guusjes post, has a contemporary Japanese Robatayaki cuisine awardwinning and has room for about 80 people in the main dining area, a few high tables with stools for 2 guests and seats around their Robatayaki grill. They also have a few tables outside on the pavement. The grill is in the center of the restaurant and the whole place has a warm contemporary feel to it. I wish I could eat here everyday', 'UK', 'London', 'w1k 6zf', '30 North Audley Street, Mayfair', NULL, NULL, NULL, 9, 9, 0, 'dinnerdate,sealthedeal,goallout', '', '', '', '', 'http://www.rokarestaurant.com/rokamayfair', 'Y', 'Y', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', '12:30:00', '23:30:00', NULL, NULL, NULL, NULL, NULL, NULL),
(16, '2015-06-25 01:05:31', '2015-06-25 01:15:12', 'da9da35ba301166f7a0af4d072cfed94', 'The WC', 'Our ever-changing wine list offers an eclectic mix of the Old and New World. We have selected our wines for their inherent interesting characteristics before anything else and we endeavour to always keep the list interesting and diverse. A competent list of classic and contemporary cocktails as well as London craft beers completes the beverage offer.You enter via the original tiled staircase, down into a dimly-lit bar hung with hams and sausages. To the left are three cosy leather banquette-lined booths for larger parties to the right are tables that benefit from a trickle of light from the thick glass squares inset in the pavement above. Cracked glazed tiles line the walls, and the original wooden urinal partitions have been commandeered as table tops after a course of disinfection, I am assured by Andy Bell and Jayke Mangion, the locals who fought off 450 applicants to revamp the space as part of the Clapham Old Town Regeneration Project. It sounds naff but isnt.  ', 'UK', 'London', 'SW4 7AA', 'Clapham Common South Side, Clapham', NULL, NULL, NULL, 9, 6, 7, 'firstdate,drinksonly,visitor,friends', '', '', '', '', 'http://www.wcclapham.co.uk/', 'Y', 'Y', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '23:30:00', '17:00:00', '00:30:00', '12:00:00', '00:30:00', '12:00:00', '22:30:00', '0000-11-30', '0000-11-30', NULL, NULL, NULL, NULL),
(17, '2015-06-26 02:19:39', '2015-06-26 02:19:39', 'f28cdb9254a50422ec212f2ba1c6b401', 'Bermondsey Arts Club', 'Weve heard of some mighty renovations in our time: pool halls into supper clubs, supper clubs into speakeasies and speakeasies into full blown modern clubs. But the Bermondsey Arts Club is the first time weve heard of a public toilet turning into a cocktail bar.It sounds like a marvellous quip from an episode of Dr Who, but it is in fact a real, fully fledged thing set to engulf Tower Bridge Roads bar scene. When you get inside, youll see little of the venues past. The interiors, which reflect a 20s art-deco theme, are the work of designers Artistic Spaces, who have installed muted tones all around the 60-cover space to give it the feel of an early century creative hangout.Brass panelings, amber glass features and walnut colours will line the walls, but its the marble-clad bar and table tops that are the real stand out features: the stone was originally used as toilet cubicle separators. But dont worry, the clubs Director George Garnier ensured us its been thoroughly washedOnce youre settled and relaxed in an entirely different way to those who visited this building in Victorian times, you can grab yourself one of the bespoke cocktails from the bar and chat about, well, the venue, wed imagine. Negronis are a speciality, as are Bermondsey Gardens and Succubus if you fancy, ahem, whetting your palate. - See more at: http://www.justopenedlondon.com/bermondsey-arts-club/sthash.LJKNIPnt.dpuf', 'UK', 'London', 'SE1 4TP', 'Former Public Conveniences, 102A Tower Bridge Road, London', NULL, NULL, NULL, 8, 5, 6, 'firstdate,drinksonly,visitor,friends', '', '', '', '', 'http://bermondseyartsclub.co.uk/', 'N', 'N', '18:00:00', '02:00:00', '18:00:00', '18:00:00', '18:00:00', '02:00:00', '18:00:00', '02:00:00', '18:00:00', '02:00:00', '18:00:00', '02:00:00', '18:00:00', '02:00:00', '18:00:00', '18:00:00', '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL),
(18, '2015-06-26 02:23:27', '2015-06-26 02:23:27', 'aa02a3731497228a7c54affea906d258', 'Camino Bankside', 'Camino Tapas has opened its doors at Bankside projecting this time an informal take on an authentic Spanish tapas.  Executive chef Nacho Del Campo, originally from the Basque country, has created a menu of tapas and sharing platters reflective of his heritage, some of which might be familiar from the sister restaurants at Kings Cross, Blackfriars and Monument.Choose from pan roasted octopus tentacle with purple potatoes or black rice with cuttlefish, squid ink and alioli, and theres a typically wide range of artisan cheeses. Occupying pride of place is the jamon counter, where you can watch the cortador de jamon exercise his skill over the acorn-fed Iberico black pig ham  cured for no less that 32 months. The bar serves Spanish craft beers as well as Madrids premium lager, Mahou, on draught, and a full Spanish brunch is available at weekends.- See more at: http://www.justopenedlondon.com/camino-bankside/sthash.dGpRKzLt.dpuf', 'UK', 'London', 'SE1 9AN', '5 Canvey Street, London', NULL, NULL, NULL, 7, 5, 3, 'dinnerdate,visitor,friends', '', '', '', '', 'http://www.camino.uk.com/restaurants/bankside/', 'Y', 'Y', '12:00:00', '00:00:00', '12:00:00', '23:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '12:00:00', '00:00:00', '11:00:00', '23:00:00', '11:00:00', '22:00:00', '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL),
(19, '2015-06-26 02:29:07', '2015-07-13 07:45:31', '569063f6bcb563515ae636e347c15468', 'The Well', 'HEAD DOWNSTAIRS FOR DRINKS, UPSTAIRS FOR EATS Brooklyns shrub cocktails are order of the day at newly-opened Hoxton, Holborn this month. Following suit is The Wells basement bar in Clerkenwell, who together with Buffalo Chase bourbon are throwing a pickle-themed party for autumn. Wooden crates and Victorian picture frames look coincidentally left down here  and this mish-mash decor creates a cosy hideaway for the whisky enthused.Slouching in comfy sofas between bespoke ornaments, The Pickle Jar encourages private parties or drop-ins to sink traditional Pickle Back shots made up of smooth bourbon and a crisp pickle juice chaser  choose from beetroot, cucumber or gherkin. The two-shot combination results in a shocking sweetness that enlivens the tastes of the bourbon. Longer shrub cocktails replace the acidity of lemon and lime with a splash of vinegar to adjust the sweetness created from the base of your drink: be it sloe berry, apricot or raspberry. Other, more typical NY cocktails are available if vinegars not your thing, and for larger parties, sliders can be booked with your drinks. The Pickle Jar is a bourbon-drinkers paradise in the basement of a Clerkenwell fave, and a perfect excuse to get pickled this autumn. - See more at: http://www.justopenedlondon.com/the-pickle-jar-at-the-well/sthash.9UJe9GRZ.dpuf', 'UK', 'London', 'EC1V 4JY', '180 St John Street, Clerkenwell', NULL, NULL, NULL, 7, 5, 6, 'firstdate,drinksonly,dinnerdate,visitor,friends,brunch', '', '', '', '', 'http://www.downthewell.co.uk/index.php/the-pickle-jar/', 'Y', 'N', '17:00:00', '00:00:00', '17:00:00', '17:00:00', '17:00:00', '17:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '00:00:00', '17:00:00', '01:00:00', '17:00:00', '01:00:00', '0000-11-30', '0000-11-30', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ds_venue_image`
--

CREATE TABLE IF NOT EXISTS `ds_venue_image` (
  `venue_id` int(10) unsigned NOT NULL,
  `venue_image_order` tinyint(3) unsigned NOT NULL,
  `venue_image_data` mediumblob NOT NULL,
  `venue_image_thumbnail_data` blob NOT NULL,
  `venue_image_data_format` varchar(64) NOT NULL,
  `venue_image_description` tinytext NOT NULL COMMENT 'A description of what the photo of the venue is showing'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Photos or images to be uploaded for a particular venue';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
