<?php
/**********************************************************************
 *
 *	script file		: class_clientsession.php
 *	
 *	begin			: 15 November 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: The hander for client session mangement
 *
 **********************************************************************/
 
 
 class UserSession
 {
	 var $token_id	= null;
	 var $user_id	= UNKNOWN_USER_ID; // by default
	 
	 // TODO: No user stuff is actually developed yet, it's all just token logging 
	 // and generation for the moment.
	 function getUserToken()
	 {
		 global $conn;
		 
		 if ( isset ($_REQUEST['token']) )
		 {
			 if ( is_numeric($_REQUEST['token']) ) // probably be a md5 hash at some point
			 {
				 $this->token_id = $_REQUEST['token'];
				 return true;
			 }
		 }
		 
		//
		// Otherwise generate a new user token, for now ever user ID is unknown
		$sql = 'INSERT INTO  '. USER_TOKEN_TABLE .'(`token_id` ,`user_id`) VALUES (NULL , '. UNKNOWN_USER_ID .')';

		try
		{		
			// Do the query
			$query = $conn->query($sql); 
			
			// Debug Mode
			if (DEBUG_MODE){ debug_message($sql); }	
				
		}
		catch(PDOException $e)
		{
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			return false; // fail.
		}
		
		 $this->token_id = $conn->lastInsertId();
		 
		 if (DEBUG_MODE){ debug_message('Token ID Generated: ' . $this->token_id); }	
		
		 return $this->token_id;
		
		
	 } // end getUserToken
	 
	 
	 function logUserSession($lat, $long)
	 {
		 global $conn;
		 
		 // AHAHAHAhHAhahahsajdakjkhfakjfhaskfhksadhf khaskdfh adsklfh sdkfh askh
		 // asjdfhaskjfhksdjhf kjash f
		 // (O_o) burhrhaharhrah WHERE ARE THEY... WHERHE ARE THEYEU!@!!! hahahaha I know where you are!!
		 // Watching you!
		 // (*_*)(O_o)(*_*)(o_O)(*_*)(O_o)(*_*)(O_o)(*_*)(o_O)(*_*)(O_o)(*_*)(O_o)(*_*)(o_O)(*_*)(O_o)

			
		if ( !is_numeric($this->token_id) ) return false;
		
		/*
		
			$calories = 150;
			$colour = 'red';
			$sth = $dbh->prepare('SELECT name, colour, calories
				FROM fruit
				WHERE calories < :calories AND colour = :colour');
			$sth->bindParam(':calories', $calories, PDO::PARAM_INT);
			$sth->bindParam(':colour', $colour, PDO::PARAM_STR, 12);
			$sth->execute();
			
		*/

		// Otherwise generate a new user token, for now ever user ID is unknown
		$sth = $conn->prepare('INSERT INTO  '. USER_SESSION_LOG_TABLE .' ( `user_id` , `user_location_date` , `user_location_lat` , `user_location_long` ) VALUES (? , NOW(), ?, ?)');
		
		try
		{		

			// Do the query
			$query = $sth->execute( array($this->user_id, $lat, $long) ); 
				
		}
		catch(PDOException $e)
		{
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			return false; // fail.
		}
	 
	 } // end logUserSession

 } // end session management