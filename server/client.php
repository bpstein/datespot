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
include('./include/class_datespot.php'); // can't do much without this one


class JSONClientHandler
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
	

	function JSONClientHandler()
	{
		switch (@$_REQUEST['a'])
		{
		
			case 'GetAll':  // NOT ACTUALLY USED
				$this->GetAll();
				break;
				
			default:
				$this->GetNearest(); // Get the Nearest Venues		
				break;
		}
		
		// Sent JsonOutput
		$this->_output_JSON();
		
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
			if (DEBUG_MODE) { echo 'Latitude and Longitude were OK.'; }
			
		}
		else
		{
			
			
		}


		// From: http://www.tec20.co.uk/working-with-postcodes-and-spatial-data-in-mysql/
		/* 	In order to perform a search on this table, we need an efficient way of searching the data. MySQL offers a number of geometry functions that we could use, but we are in dangerous territory of writing an inefficient query. An obvious choice is to use GLength(), which calculates the distance between two points. The down side is that it’s impossible to make use of any indexes, as we would have to calculate a value for each postcode stored in the database. Searching against 1,700,000 entries would take far too long to return a value. In order to reduce the collation down, we can restrict the search with a bounding box. Our solution is going to present map that you click on, so we can get the current bounding box that the map covers. As we can get the minimum and maximum coordinates from the current map, we can use this to filter the database table down using MBRWithin() before we start performing GLength() operations. This will use the SPATIAL index and speed up our query significantly. An explanation of how the spatial index works in this scenario can be found in the MySQL documentation. */	
		
		// This query OMITS an ENVELOPE, so it is unbounded and will search the entire database space (small).
		// might need to bound the search space at a later date with the below WHERE clause.
		/* 			WHERE
				MBRWITHIN(Point, ENVELOPE(GEOMFROMTEXT('LINESTRING(" . $minLat . " "
									. $minLong . ", ". $maxLat . " " . $maxLong
									. &quot;)')))
		*/
		
		
		// We leverage MySQL Geometric and Spatial Index column here. Whilst the latitude and longitude are stored separately as well, we do not use them for the purposes of this query.
		$sql = 'SELECT venue_name as Name, X(venue_location_spatial_point) Latitude, Y(venue_location_spatial_point) Longitude, venue_address as Address, venue_postcode as Postcode, venue_description as Description, GLENGTH(GEOMFROMTEXT(CONCAT(\'LINESTRING(' . $_origin_lat . ' '. $_origin_long. ',\' ,X(venue_location_spatial_point),\' \',Y(venue_location_spatial_point),\')\'))) AS SpatialDistance
				FROM       '. VENUE_TABLE .'
				WHERE venue_location_spatial_point IS NOT NULL
				ORDER BY SpatialDistance ASC';
				
		if ($nolimit == false)
		{
			$sql .= ' LIMIT 10'; 
		}
		
				
		try
		{		
			// Do the query
			$query = $conn->query($sql);
			$count = 1;
			while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
				$this->result_array[$count++] = $data;
			}
			
			// Debug Mode
			if (DEBUG_MODE)
			{ echo $sql; }	
				
		}
		catch(PDOException $e)
		{
			$this->success = false;
			$this->error_messages[] =  'Failed to execute database query: ' . $e->getMessage();
	
		}
		
	}
	
	function GetAll()
	{
		global $conn;

	}
	
	// That the result of the activities undertaken in this class and spit out the result
	function _output_JSON()
	{

		$response = array('success' => $this->success, 'points' => $this->result_array, 'error' => implode(', ', $this->error_messages));
		header('Content-Type: application/json');
		
		echo json_encode(utf8_encode_all($response));
		
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

			
		//  	In order to perform a search on this table, we need an efficient way of searching the data. MySQL offers a number of geometry functions that we could use, but we are in dangerous territory of writing an inefficient query. An obvious choice is to use GLength(), which calculates the distance between two points. The down side is that it’s impossible to make use of any indexes, as we would have to calculate a value for each postcode stored in the database. Searching against 1,700,000 entries would take far too long to return a value. In order to reduce the collation down, we can restrict the search with a bounding box. Our solution is going to present map that you click on, so we can get the current bounding box that the map covers. As we can get the minimum and maximum coordinates from the current map, we can use this to filter the database table down using MBRWithin() before we start performing GLength() operations. This will use the SPATIAL index and speed up our query significantly. An explanation of how the spatial index works in this scenario can be found in the MySQL documentation. 
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
 *
 * Start the class and processing the actions
 * TODO: Session handling??  FB integration, everything.
 *
 */

}
 
new JSONClientHandler();



// http://stackoverflow.com/questions/5756232/moving-lat-lon-text-columns-into-a-point-type-column
// http://stackoverflow.com/questions/28917183/using-mysql-point-and-php-to-insert-latitude-and-longitude-points-through-a-fo
// http://stackoverflow.com/questions/159255/what-is-the-ideal-data-type-to-use-when-storing-latitude-longitudes-in-a-mysql


