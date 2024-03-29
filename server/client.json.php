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
define ('IN_APPLICATION', 	TRUE);
define ('DEBUG_MODE', 		FALSE);

include('./include/common.php');
include('./include/class_clientsession.php');

// Allow accesss to this script from another domain for development.
header('Access-Control-Allow-Origin: http://localhost:8100');


// Example URLS:
// http://192.168.0.17/client.php?originLat=51.462165299999995&originLong=-0.1691684
// http://192.168.0.17/client.php?a=img&viuid=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa


$ClientDateTime = new DateTime('NOW'); 

class ClientHandler extends UserSession
{

	/********************************************************************************
	 *
	 *	function	: JSONQueryHandler
	 *  purpose     : Entry point for client requests to the database. This is where
	 *	 			  more of the logic has to be developed especially in regards to 
	 *				  mobile client actions and session management.
	 *
	 ********************************************************************************
	 */
	 
	// Change as appropriate
	private $venue_image_url_base =	'http://ds.urandom.info/client.json.php?a=img&viuid=';
    //var $venue_image_url_base	=	'http://192.168.56.101/client.json.php?a=img&viuid=';
	private $limit 	= 10; // Maximum number of results in one hit
	private $offset = 0;
	
	 
    // Variables used internally
	private $success 		= false;	// By default, it's a fail until proven otherwise.
	private $error_message	= '';
	private $JSON_result_array 	= array();	// What we JSON encode and send back to the client.
										// Make sure this is only a single array list or we'll break the JSON (it'll return an object) and fuck everything up	
										
	protected $venue_base_sql_select_attributes	= '*,
											   v.venue_id,
											   v.venue_unique_id 			AS vuid,
											   v.venue_name 				AS name,
											   v.venue_description			AS desc_long,
											   v.venue_description_short	AS desc_short,
											   v.venue_address				AS address,
											   v.venue_postcode			 	AS postcode,
											   v.venue_location_lon			AS longitude,
											   v.venue_location_lat			AS latitude,
											   CEILING(v.venue_rating_cost/5) AS rating_cost';
											   
	// What we permit to be sent via JSON from results array (whitelist)
	protected $venue_base_sql_select_attributes_JSON_whitelist = array('vuid' => 1, 'name' => 1, 'desc_long' => 1, 'desc_short' => 1, 'address' => 1, 'postcode' => 1, 'longitude' => 1, 'latitude' => 1, 'rating_cost' => 1);										   
						
    // Timezone class
	//private $datetime;
	private $client_day;
	private $client_time; // What is the client's timzone?

	function ClientHandler()
	{

		/**************************************************
		 JSONClientHandler, $_REQUEST query variables:
		
		 a 			= action	
		 originLat	= Latitude
		 originLong = Longitude
		 viuid		= The venue_image_unique_id
		 sid		= scenario/situation id					
		 o			= offset (MySQL Start and End Point)		
		 nolimit	= no limit (no MySQL Start and End Point)
		 ver		= Client Version
		 
		 // In ClientSession
		 token		= Token ID
		 
		 */
		
		// Get our datetime
		//$datetime = new DateTime('NOW');
		 
		// Calculate offset if required for sql
		if ( isset($_REQUEST['o']) )
		{
				$offset = $_REQUEST['o'];
				if ( is_numeric($offset) )
				{
						if ($offset > $limit) $this->offset = $offset;
				}					
		} // end offset calc
		
		
		switch (@$_REQUEST['a'])
		{

			// Get venue image binary
			case 'img':
				$this->GetImage();
				exit(); // get straight out after sending binary
				break;
				
		
			// For testing purposes only - Get ALL venues
			case 'all': 
				$this->GetAll(false);
				break;

			// Get the nearest and suggested venues
			default:
				$this->GetSuggested(); // Get the Suggested Venues		
				break;
		}
		
		$this->_output_JSON();
		
		// Get outta here.
		exit();

	} // init function for class
	
	
	// Our first ever function to get the venues... we'll start with everything for the time being.
	function GetSuggested()
	{
		global $conn;
		
		// What is the client version, needs to be defined
		if ( !isset($_REQUEST['ver']) || ($_REQUEST['ver'] < 2)  )
		{
			$this->error_message = 'Please upgrade your client.';
			return false;
		}
		
		// Mandatory Requirements
		$_origin_lat 	= clean_string(@$_REQUEST['originLat']); 	// should keep 0.00 etc.
		$_origin_long 	= clean_string(@$_REQUEST['originLong']); 	// should keep 0.00 etc.
		$_scenario 		= clean_string(@$_REQUEST['sid']); 			// firstdate etc.

		// $_scenario = null;
		
		// lat and log co-ords OK?
		// http://stackoverflow.com/questions/5756232/moving-lat-lon-text-columns-into-a-point-type-column
		if ( is_numeric($_origin_lat) && is_numeric($_origin_long)   )
		{
			if (DEBUG_MODE) { debug_message('Latitude and Longitude were OK.'); }
		}
		else
		{	
			if (DEBUG_MODE) { debug_message('Latitude and Longitude were not OK.'); }
			$this->error_message = 'Your position could not be found. Please try again later';
			
			return false; // FAIL - exit function here
		}

		if (DEBUG_MODE) { debug_message('Requested updated to Latitude: '. $_origin_lat .' Requested updated to Longitude: '. $_origin_long ); }		


		// From: http://www.tec20.co.uk/working-with-postcodes-and-spatial-data-in-mysql/
		/* 	In order to perform a search on this table, we need an efficient way of searching the data. MySQL offers a number of geometry functions that we could use, but we are in dangerous territory of writing an inefficient query. An obvious choice is to use GLength(), which calculates the distance between two points. The down side is that itÃ¢â‚¬â„¢s impossible to make use of any indexes, as we would have to calculate a value for each postcode stored in the database. Searching against 1,700,000 entries would take far too long to return a value. In order to reduce the collation down, we can restrict the search with a bounding box. Our solution is going to present map that you click on, so we can get the current bounding box that the map covers. As we can get the minimum and maximum coordinates from the current map, we can use this to filter the database table down using MBRWithin() before we start performing GLength() operations. This will use the SPATIAL index and speed up our query significantly. An explanation of how the spatial index works in this scenario can be found in the MySQL documentation. */	
		
		// This query OMITS an ENVELOPE, so it is unbounded and will search the entire database space (small).
		// might need to bound the search space at a later date with the below WHERE clause.
		/* 			WHERE
				MBRWITHIN(Point, ENVELOPE(GEOMFROMTEXT('LINESTRING(" . $minLat . " "
									. $minLong . ", ". $maxLat . " " . $maxLong
									. &quot;)')))
		*/
		
		
		/***
		 * We leverage MySQL Geometric and Spatial Index column here. Whilst the latitude and longitude are stored separately as well, we do not use them for the purposes of this query.
		 * Also note that MySQL's spatial functions X() and Y(), stores the Latitude (North to South) as the X, and Longitude as the Y, which is contrary to what you think 
		 * think it should be. ie. Y = Up, so should be Latitude. Not too worry, the main thing is it comes out as at the right key=>value pair.
		 */
		$sql = 'SELECT '. $this->venue_base_sql_select_attributes .'
				FROM   '. VENUE_TABLE .' v
				WHERE v.venue_location_spatial_point IS NOT NULL ';
		
		// If we have a scenario
		if ( !empty($_scenario) ) $sql .= 'AND v.venue_scenario LIKE \'%'. $_scenario .'%\' ';
		
		$sql .= ' ORDER BY GLENGTH(GEOMFROMTEXT(CONCAT(\'LINESTRING(' . $_origin_lat . ' '. $_origin_long. ',\' ,X(venue_location_spatial_point),\' \',Y(venue_location_spatial_point),\')\'))) ASC';
			
		// Limit results as required	
		if ( !isset($_REQUEST['nolimit']) )
		{
			$sql .= ' LIMIT '. $this->offset .', '. $this->limit; 
		}
			
		try
		{		
			// Do the query
			$query = $conn->query($sql); $count = 1;
			while ($data = $query->fetch(PDO::FETCH_ASSOC)) 
			{ 
				 $result = array();

				 // Build Images
  				 $result['images'] 			= $this->_get_venue_image_URLs($data['venue_id']);
				 $result['image_url'] 		= $result['images'][0]; // The first main image, other the default as _get_venue_image_URLs will always return at least one image
				 
				 // Build Distance Data
				 //$_distance_in_miles		= distance($data['latitude'], $data['longitude'], $_origin_lat, $_origin_long, 'M');
				 //$_distance_in_km			= $_distance_in_miles * 1.609344; // we do this here to avoid a complete recalcuation in the distance() function.
				 
				 //$_distance_in_miles		= round($_distance_in_miles, 2);
				 //$_distance_in_km			= round($_distance_in_km, 2);

				 //$result['distance_miles'] 		= $_distance_in_miles;
				 //$result['distance_km'] 		= $_distance_in_km;
				 
				 // TODO Re-weight the rankings
				 //$result['dsr']
				 //`venue_rating_general` ,  `venue_rating_cost` ,  `venue_rating_quirkiness` 
				 
				 // Gotta love PHP
				 $result['rating_cost_html_str']		= ($data['rating_cost'] != 0) ? str_repeat("&pound; ", $data['rating_cost']):'???'; // Question marks if unknown
				 
				 $result['venue_open_now'] = $this->_venue_is_open_now($data);
				 
				 
				// IMPORTANT: We don't want to expose this data in JSON
				//unset($data['latitude']); unset($data['longitude']);
				// unset($data['venue_id']);
				// unset($data['rating_cost']);
				//http://php.net/manual/en/function.array-intersect-key.php
				$data = array_intersect_key($data, $this->venue_base_sql_select_attributes_JSON_whitelist);
				 
				// Build the specific JSON results array;
				$this->JSON_result_array[] = array_merge($result, $data);
				 
				 
				//$this->JSON_result_array[] = $this->_venue_data_last_chance_saloon($data); 	
				
			} // end the loop through each event
			
			// Debug Mode
			if (DEBUG_MODE){ debug_message($sql); }	
				
		}
		catch(PDOException $e)
		{
			$this->success = false;
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			
			return false; // fail.
		}
		
		
		//
		// Start User Session / Return Token if required
		$this->getUserToken();
		$this->logUserSession($_origin_lat, $_origin_long);
		




		// Happy Days
		$this->success = true;


		// Sent JsonOutput
		//$this->_output_JSON();		

	} // end GetSuggested
	
	
	// Only used for testing now. Not to be used for the app
	function GetAll()
	{
		global $conn;
		
		// We need to make sure a venue doesn't have multiple images with a rank of 0 or we'll get duplicates!
		$sql = 'SELECT '. $this->venue_base_sql_select_attributes .'
				FROM   '. VENUE_TABLE .' v LEFT JOIN '. VENUE_IMAGE_TABLE .' vi ON v.venue_id = vi.venue_id
				GROUP BY v.venue_id';
	
		try
		{		
			// Do the query
			$query = $conn->query($sql); $count = 1;
			while ($data = $query->fetch(PDO::FETCH_ASSOC)) 
			{ 
				$this->JSON_result_array[] = $this->_venue_data_last_chance_saloon($data); 
			}
			
			// Debug Mode
			if (DEBUG_MODE){ debug_message($sql); }	
				
		}
		catch(PDOException $e)
		{
			$this->success = false;
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			
			return false; // fail.
		}
		
		// Sent JsonOutput
		//$this->_output_JSON();	

		// Happy Days
		$this->success = true;
		
	} // end GetNearest
	
	
	function GetImage()
	{
		global $conn;
		
		
		// Check that a valid image has been provided
		if ( !isset($_REQUEST['viuid']) )
		{
			if (DEBUG_MODE) { debug_message('No viuid provided.'); }
			return false;
		}
		
		// Check to see if it's clean
		if ( preg_match('/[a-zA-Z0-9]{32}/', $_REQUEST['viuid']) !== 1)
		{
			if (DEBUG_MODE) { debug_message('Invalid viuid provided. Nice one...'); }
			return false;		
		}
		
		// Get resized square binary compressed image.
        $sql = 'SELECT venue_image_data_resized_square
				FROM '. VENUE_IMAGE_TABLE .' 
				WHERE venue_image_unique_id = \''. $_REQUEST['viuid'] .'\'';
				
        if (DEBUG_MODE) { debug_message($sql); }
		
		try
		{	
			$query          = $conn->query($sql);
			$image_data     = $query->fetch();
		}
		catch(PDOException $e)
		{
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			return false; // fail.
		}

        // Exit if the image data is empty
        if (empty($image_data)) 
		{ 
			if (DEBUG_MODE) { debug_message('No image of the requested Unique ID exists in the database.'); }
			return false;
		}


        // We'll be outputting an image of type.... like a JPEG
        header('Content-Type: '. $image_data['venue_image_data_format']);	
		echo $image_data['venue_image_data_resized_square'];
		
	} // end GetImage
	
	

	
	
	/*************************************************************************************
	 * Internal Functions
	 *************************************************************************************/
	
	function _get_venue_image_URLs($venue_id)
	{
		global $conn;	
	
		// We need to make sure a venue doesn't have multiple images with a rank of 0 or we'll get duplicates!
		$sql = 'SELECT vi.venue_image_unique_id 
				FROM '. VENUE_IMAGE_TABLE .' vi  
				WHERE vi.venue_id = '. $venue_id .' 
				ORDER BY vi.venue_image_order ASC';
	
		try
		{		
			// Do the query
			$query = $conn->query($sql); $count = 0; $images = array();
			while ($image = $query->fetch(PDO::FETCH_ASSOC)) 
			{ 
				$count++;
				$images[] = $this->__generate_venue_image_url($image['venue_image_unique_id']);
			}
			
			// Debug Mode
			if (DEBUG_MODE){ debug_message($sql); }	
				
		}
		catch(PDOException $e)
		{
			if (DEBUG_MODE){ debug_message('Failed to execute database query: ' . $e->getMessage()); }	
			return false; // fail.
		}
		
		// Don't have a single image for this event? Send the questions mark!
		if ( $count == 0 )
		{
			$images[] = $this->__generate_venue_image_url('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
			
		}
		
		return $images;
		
	} // _get_venue_images
	
	
	// Check to see if a venue is open
	function _venue_is_open_now($data)
	{
		global $ClientDateTime;
		
		// http://php.net/manual/en/datetime.settimezone.php
		// http://php.net/manual/en/class.datetime.php

		// TODO: Need to factor in the timezone of the venue!!
		if ( !isset($this->client_time) )
		{
			$ClientDateTime->setTimezone(new DateTimeZone('Europe/London')); // We will get the timezone from the first result
	
			$this->client_time 	= $ClientDateTime->format('H:i:s'); // return 21:07:56
			$this->client_day 	= strtolower($ClientDateTime->format('l')); // return 'friday', array elements are case sensitive
		}
		
		// print $ClientDateTime->format('Y-m-d H:i:s (e)');
		
		// Todo, need to factor in venues timezone
		return $this->__isBetween($data['venue_hour_'. $this->client_day.'_open'], $data['venue_hour_'. $this->client_day .'_close'], $this->client_time);
	}
	
	
	// From: http://stackoverflow.com/questions/27131527/php-check-if-time-is-between-two-times-regardless-of-date/27134087#27134087
	function __isBetween($from, $till, $input, $venue_tz = null) 
	{
		// echo 'The venue open time:'. $from;
		// echo 'The venue close time:'. $till;

		$f = DateTime::createFromFormat('!H:i:s', $from);
		$t = DateTime::createFromFormat('!H:i:s', $till);
		$i = DateTime::createFromFormat('!H:i:s', $input);
	
		if ($f > $t) $t->modify('+1 day');
		return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
	}

	
	 
	 // Check the venue row to see if there's anything we need to fix before it gets JSONified
	 // Depreciated function
	function _venue_data_last_chance_saloon($row)
	{
	
		// Check that the image exists
		if ( array_key_exists('venue_image_unique_id', $row) )
		{
			// Well, an image doesn't for that particular venue or event so we need to send them a default venue thumbnail
			// Somebody fked up the data entry it would seem.
			if ( empty($row['venue_image_unique_id']) )
			{
				$row['image_url'] = $this->__generate_venue_image_url('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
			}
			else
			{
				$row['image_url'] = $this->__generate_venue_image_url($row['venue_image_unique_id']);
			}

		   // remove this from the output
		   unset($row['venue_image_unique_id']);
		   unset($row['image_order']);
		}

		return $row;
		
	}
	
	function __generate_venue_image_url($venue_image_unique_id)
	{
		// return something like http://ds.urandom.info/client.php?a=img&viuid=664efafcf73f52bea807e0c3cb1aff0a
		return $this->venue_image_url_base .''. $venue_image_unique_id;
	}

	// That the result of the activities undertaken in this class and spit out the result
	function _output_JSON()
	{
		
		$response = array('success' => $this->success, 'queryresults' => $this->JSON_result_array, 'error_message' => $this->error_message, 'token' => $this->token_id);
		
		header('Content-Type: application/json');
		echo json_encode(utf8_encode_all($response));
		
	}	
	


/*
 *
 * Start the class and processing the actions
 * TODO: Session handling??  FB integration, everything.
 *
 */

} // end of ClientHandler class
 
 
// Let us do this.
new ClientHandler();







// http://stackoverflow.com/questions/5756232/moving-lat-lon-text-columns-into-a-point-type-column
// http://stackoverflow.com/questions/28917183/using-mysql-point-and-php-to-insert-latitude-and-longitude-points-through-a-fo
// http://stackoverflow.com/questions/159255/what-is-the-ideal-data-type-to-use-when-storing-latitude-longitudes-in-a-mysql


/*	
	//http://www.tec20.co.uk/working-with-postcodes-and-spatial-data-in-mysql/
	function _fetch_nerest_leaflet_example()
	{
	
		$minLat = $_GET['minLat'];
		$minLong = $_GET['minLong'];
		$maxLat = $_GET['maxLat'];
		$maxLong = $_GET['maxLong'];
		$originLat = $_GET['originLat'];
		$originLong = $_GET['originLong'];
		 
		$success = true;
		$error = '';
		$points = array();
		 
		// Validate the input parameters
		 
		if (!isset($minLat) || !is_numeric($minLat)) {
			$error .= 'minLat was not provided or was invalid. ';
			$success = false;
		}
		 
		if (!isset($minLong) || !is_numeric($minLong)) {
			$error .= 'minLong was not provided or was invalid. ';
			$success = false;
		}
		 
		if (!isset($maxLat) || !is_numeric($maxLat)) {
			$error .= 'maxLat was not provided or was invalid. ';
			$success = false;
		}
		 
		if (!isset($maxLong) || !is_numeric($maxLong)) {
			$error .= 'maxLong was not provided or was invalid. ';
			$success = false;
		}
		 
		if (!isset($originLat) || !is_numeric($originLat)) {
			$error .= 'originLat was not provided or was invalid. ';
			$success = false;
		}
		 
		if (!isset($originLong) || !is_numeric($originLong)) {
			$error .= 'originLong was not provided or was invalid. ';
			$success = false;
		}
		 
		// Ensure our bounding box isn't too big
		// GB: I don't quite understand the math behind this 
		$size = sqrt(pow($maxLat - $minLat, 2) + pow($maxLong - $minLong,2));
		if($size > 1) {
			$success = false;
			$error = 'The bounding box is too large, please try a smaller area. ';
		}
		 
		if ($success) {
			$pdo = new PDO('mysql:host=127.0.0.1;dbname=Postcode', 'user', 'password');
		 
			$stmt = $pdo->prepare(
			'SELECT
				Name,
				X(Point) Latitude,
				Y(Point) Longitude
			FROM
				`PostcodePoint`
			WHERE
				MBRWITHIN(Point, ENVELOPE(GEOMFROMTEXT(\'LINESTRING(" . $minLat . " " . $minLong . ", ". $maxLat . " " . $maxLong . ")\')))
			ORDER BY   GLENGTH(GEOMFROMTEXT(CONCAT(\'LINESTRING(" . $originLat . " " . $originLong . ", \' ,X(Point),\' \',Y(Point),\')\')))
			LIMIT 10');
		 
			if (!$stmt->execute(array($postcode))) {
				$error = 'Database failed with error code: '.$stmt->errorCode();
				$success = false;
			} else {
				while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$points[$data['Name']] = $data;
				}
			}
		}
		$response = array('success' => $success, 'points' => $points, 'error' => $error);
		header('Content-Type: application/json');
		echo json_encode($response);

			
		//  	In order to perform a search on this table, we need an efficient way of searching the data. MySQL offers a number of geometry functions that we could use, but we are in dangerous territory of writing an inefficient query. An obvious choice is to use GLength(), which calculates the distance between two points. The down side is that itÃ¢â‚¬â„¢s impossible to make use of any indexes, as we would have to calculate a value for each postcode stored in the database. Searching against 1,700,000 entries would take far too long to return a value. In order to reduce the collation down, we can restrict the search with a bounding box. Our solution is going to present map that you click on, so we can get the current bounding box that the map covers. As we can get the minimum and maximum coordinates from the current map, we can use this to filter the database table down using MBRWithin() before we start performing GLength() operations. This will use the SPATIAL index and speed up our query significantly. An explanation of how the spatial index works in this scenario can be found in the MySQL documentation. 
	}
	
	*/
	


		/* {"success":true,
			"points":{
				"B5 4BU":{
					"Name":"B5 4BU",
					"Latitude":"52.47842",
					"Longitude":"-1.89425"
				},
				"B4 7SD":{
					"Name":"B4 7SD",
					"Latitude":"52.47865",
					"Longitude":"-1.89374"},
				
				"B4 7SE":{"Name":"B4 7SE","Latitude":"52.47865","Longitude":"-1.89374"},"B4 7SF":{"Name":"B4 7SF","Latitude":"52.47865","Longitude":"-1.89374"},"B4 7SH":{"Name":"B4 7SH","Latitude":"52.47865","Longitude":"-1.89374"},"B4 7SL":{"Name":"B4 7SL","Latitude":"52.47865","Longitude":"-1.89374"},"B4 7TE":{"Name":"B4 7TE","Latitude":"52.47894","Longitude":"-1.89489"},"B2 4LE":{"Name":"B2 4LE","Latitude":"52.47859","Longitude":"-1.89543"},"B4 7SY":{"Name":"B4 7SY","Latitude":"52.47969","Longitude":"-1.89423"},"B4 7SS":{"Name":"B4 7SS","Latitude":"52.47955","Longitude":"-1.89364"}},
			"error":""}
		 */



/*

// EXAMPLE BELOW WHERE WE ARE SELECTING LAT AND LONG FROM TABLE USING X() and Y() SPATIAL FUNCTION

// We leverage MySQL Geometric and Spatial Index column here. Whilst the latitude and longitude are stored separately as well, we do not use them for the purposes of this query.
$sql = 'SELECT 'v.venue_name as Name, X(v.venue_location_spatial_point) Latitude, Y(v.venue_location_spatial_point) Longitude, v.venue_address as Address, v.venue_postcode as Postcode, v.venue_description as Description, v.venue_rating_general AS GenRating, v.venue_rating_cost AS CostRating, v.venue_rating_quirkiness AS QuirkinessRating
		FROM       '. VENUE_TABLE .' v LEFT JOIN '. VENUE_IMAGE_TABLE .' vi ON v.venue_id = vi.venue_id
		WHERE venue_location_spatial_point IS NOT NULL AND (vi.venue_image_order = '. VENUE_PRIMARY_MUGSHOT .' OR vi.venue_image_order IS NULL)
		ORDER BY GLENGTH(GEOMFROMTEXT(CONCAT(\'LINESTRING(' . $_origin_lat . ' '. $_origin_long. ',\' ,X(venue_location_spatial_point),\' \',Y(venue_location_spatial_point),\')\'))) ASC';
		// We need to make sure a venue doesn't have multiple images with a rank of 0 or we'll get duplicates!
				
*/



/// Calculate Distance
//http://www.movable-type.co.uk/scripts/latlong.html

