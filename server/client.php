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

include('./include/common.php');

// Example URLS:
// http://192.168.0.17/client.php?originLat=51.462165299999995&originLong=-0.1691684
// http://192.168.0.17/client.php?a=img&viuid=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa


class ClientHandler
{

	/********************************************************************************
	 *
	 *	function	: JSONQueryHandler
	 *  purpose     : Entry point for client requests to the database. This is where
	 *	 			  more of the logic has to be developed especially in regards to 
	 *				  mobile client actions and session management. Current actions:
	 *
	 ********************************************************************************
	 */
	 
    // Variables used internally
	var $success 		= true;	// We'll be positive and assume all is well.
	var $error_messages	= array();
	var $result_array 	= array();
	
	var $venue_base_sql_select_attributes	= 'v.venue_unique_id 		AS vuid,
											   v.venue_name 			AS name,
											   v.venue_description		AS description,
											   v.venue_address			AS address,
											   v.venue_location_lon		AS longitude,
											   v.venue_location_lat		AS latitude, vi.venue_image_unique_id, MIN(vi.venue_image_order) AS image_order'; // base attributes that we query across the board
											   
								
	// Change as appropriate
	var $venue_image_url_base =	'http://ds.urandom.info/client.php?a=img&viuid=';

	function ClientHandler()
	{

		/**************************************************
		 JSONClientHandler, $_REQUEST query variables:
		
		 a 			= action		
		 originLat	= Latitude
		 originLong = Longitude
		 viuid		= The venue_image_unique_id
		 
		 */

		 
		switch (@$_REQUEST['a'])
		{
	
			// For testing purposes only - Get ALL venues
			case 'all': 
				$this->GetAll();
				break;
				
			// Get venue image binary
			case 'img':
				$this->GetImage();
				break;
				
			// Get the nearest venues
			default:
				$this->GetNearest(); // Get the Nearest Venues		
				break;
		}
		
		// Get outta here.
		exit();

	} // init function for class
	
	
	// Our first ever function to get the venues... we'll start with everything for the time being.
	function GetNearest($nolimit = true)
	{
		global $conn;
		
		$_origin_lat = clean_string(@$_REQUEST['originLat']); // should keep 0.00 etc.
		$_origin_long = clean_string(@$_REQUEST['originLong']); // should keep 0.00 etc.	

		if (DEBUG_MODE) { debug_message('Requested updated to Latitude: '. $_origin_lat .' Requested updated to Longitude: '. $_origin_long ); }			

		
		// lat and log co-ords OK?
		// http://stackoverflow.com/questions/5756232/moving-lat-lon-text-columns-into-a-point-type-column
		if ( is_numeric($_origin_lat) && is_numeric($_origin_long)   )
		{
			if (DEBUG_MODE) { debug_message('Latitude and Longitude were OK.'); }
		}
		else
		{
		
			$this->success = false;		
			if (DEBUG_MODE) { debug_message('Latitude and Longitude were not OK.'); }
			
			return false; // exit function here
		}


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
		 
		// We need to make sure a venue doesn't have multiple images with a rank of 0 or we'll get duplicates!
		$sql = 'SELECT '. $this->venue_base_sql_select_attributes .'
				FROM   '. VENUE_TABLE .' v LEFT JOIN '. VENUE_IMAGE_TABLE .' vi ON v.venue_id = vi.venue_id
				WHERE venue_location_spatial_point IS NOT NULL 
				GROUP BY v.venue_id
				ORDER BY GLENGTH(GEOMFROMTEXT(CONCAT(\'LINESTRING(' . $_origin_lat . ' '. $_origin_long. ',\' ,X(venue_location_spatial_point),\' \',Y(venue_location_spatial_point),\')\'))) ASC';
				
				
		if ($nolimit == false)
		{
			$sql .= ' LIMIT 10'; 
		}
		
				
		try
		{		
			// Do the query
			$query = $conn->query($sql); $count = 1;
			while ($data = $query->fetch(PDO::FETCH_ASSOC)) 
			{ 
				$this->result_array[] = $this->_venue_data_last_chance_saloon($data); 
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
		$this->_output_JSON();		

	} // end GetNearest
	
	
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
				$this->result_array[] = $this->_venue_data_last_chance_saloon($data); 
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
		$this->_output_JSON();		

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
		
        $sql = 'SELECT * FROM '. VENUE_IMAGE_TABLE .' WHERE venue_image_unique_id = \''. clean_string($_REQUEST['viuid']) .'\'';
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
		
		echo $image_data['venue_image_thumbnail_data'];

		return true;

	} // end GetImage
	
	

	
	
	/*************************************************************************************
	 * Internal Functions
	 *************************************************************************************/
	 
	 // Check the venue row to see if there's anything we need to fix before it gets JSONified
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

		$response = array('success' => $this->success, 'points' => $this->result_array);
		
		if ( !isset($_REQUEST['nojsonheader']))
		header('Content-Type: application/json');
		
		header('Access-Control-Allow-Origin: http://localhost:8100');
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


