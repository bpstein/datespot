<?php
/**********************************************************************
 *
 *	script file		: common.php	
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *
 **********************************************************************/

if ( !defined('IN_APPLICATION') )
{
	die('Error. This file is not directly accessed.');
}

if (DEBUG_MODE)
{
	echo "Debug Mode enabled.\r\n <br />";
	
	ini_set('display_errors', 1); 
	error_reporting (E_ALL);
}
else
{
	error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
}


//
// Include system configuration
//
include('config.php');
include('constants.php'); // System constants
include('functions.php'); // Gen functions



try 
{
    $conn = new PDO("mysql:host=$db_server;dbname=$db_name", $db_username, $db_password);
	
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	if (DEBUG_MODE) { debug_message('Connected successfully to the database.'); }  
}
catch(PDOException $e)
{
    echo debug_message('Connection to database failed: ' . $e->getMessage());
}

