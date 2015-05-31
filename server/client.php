<?php
/**********************************************************************
 *
 *	script file		: client.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: The hander for client requests - namely in JSON.
 *
 **********************************************************************/
 
//
// Page initiation
//
define ('IN_APPLICATION', TRUE);
define ('DEBUG_MODE', FALSE);

include('include/common.php');
include('include/class_datespot.php'); // can't do much without this one


//
// What is the JSON Query Action wanted from us?
//

// HTTP $_GET or $_POST 'a' variable is the 'action
if ( !isset($_REQUEST['a']) )
{
	if (DEBUG_MODE) { debug_message('No request action was provided. Exiting.'); }
	exit();
	
}

/* This is the JSON Query Handler from either the mobile APP or back end */
class JSONQueryHandler
{
	
	/********************************************************************************
	 *
	 *	function	: JSONQueryHandler
	 *  purpose     : Entry point for client requests to the database. This is where
	 *	 			  more of the logic has to be developed especially in regards to 
	 *				  mobile client actions and session management. Current actions:
	 *
	 *  			  query 	- Get venue data based on criteria.
	 *				  image 	- Pull the associated image for a venue from the DB.
	 *
	 ********************************************************************************
	 */
	
	function JSONQueryHandler()
	{
		switch ($_REQUEST['a'])
		{
			case 'query':
				$this->get_venue_json();
				break;
			
			default:
				echo 'FAIL';
		}
	}
	
	
	// Need to ensure values return from the database are UTF8 encoded
	function get_venue_json($venue_id = null)
	{
		
		$rows = DateSpot::get_venue($venue_id);
		
		/*
		foreach ($rows AS $row)
		{
			$_row = array();
			
			foreach ($)
			echo 'VENUE!!!!!!!! ';
		}
		
		if (DEBUG_MODE) print_r($data);
		
		// return json_encode();
		
		
		$data = array('key' => utf8_encode('This is the string') );
		*/

		echo json_encode(utf8_encode_all($rows));
		
	} // get_venue_json
	
	

}



/*
 *
 * Start the class and processing the actions
 * TODO: Session handling??  FB integration, everything.
 *
 */
 
new JSONQueryHandler();

