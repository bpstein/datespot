# datespot

This is the README  for the datespot project.


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
  PRIMARY KEY (`venue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='The datespot venues. The proprietary information is here.' AUTO_INCREMENT=1 
