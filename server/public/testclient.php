<?php
/**********************************************************************
 *
 *	script file		: testclient.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: A dirty dirty hack job of HTML and PHP in a single class
 *					  to produce the administrative backend.
 *
 **********************************************************************/
 
//
// Page initiation
//
define ('IN_APPLICATION', TRUE);
define ('DEBUG_MODE', FALSE);

include('include/common.php');


//
// Pull everything and send to client - dirty hack
//
	$sql = 'SELECT venue_name AS name, venue_address AS address, \'https://i.scdn.co/image/729ce380f5db3ccef45513364e9fb4563703bfda\' AS image_large 
			FROM '. VENUE_TABLE;
			
	$query 		= $conn->query($sql);
	
	$json_response = array(); // what we are sending 
	while($venue = $query->fetch(PDO::FETCH_ASSOC))
	{
		$json_response[] = $venue;	
	}
	
	
	// Output
	header('Content-Type: application/json');
	echo stripslashes(json_encode(utf8_encode_all($json_response)));

	