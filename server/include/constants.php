<?php
/**********************************************************************
 *
 *	script file		: constants.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: Application and Server Constants 
 *
 **********************************************************************/


if ( !defined('IN_APPLICATION') )
{
	die('Error. This file is not directly accessed.');
}

// Database Table Names
define('VENUE_TABLE',							$table_prefix.'venue');
define('USER_TABLE',					$table_prefix.'user');
define('USER_EVENT_LOG_TABLE',				$table_prefix.'user_event_log');
define('USER_LOCATION_TABLE', 				$table_prefix.'user_location');
define('USER_SESSION_TABLE',					$table_prefix.'user_session');
define('USER_SESSION_VENUE_SELECTION_TABLE',	$table_prefix.'user_session_venue_selection');
