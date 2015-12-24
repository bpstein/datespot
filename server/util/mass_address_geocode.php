<?php
/**********************************************************************
 *
 *	script file		: index.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: A dirty dirty hack job of HTML and PHP in a single class
 *					  to produce the administrative back end.
 *
 **********************************************************************/
 
//
// Page initiation
//
define ('IN_APPLICATION', TRUE);
define ('DEBUG_MODE', FALSE);

include('../include/common.php');
include('../include/class_datespot.php'); 		// can't do much without this one
include('../include/class_geocoding.php'); 		// for geocoding

	echo '<html><head><title>Mass Image GeoCoder</title></head><body>';
	

	// Get dubious venues
	$sql = 'SELECT venue_id, venue_name, venue_address, venue_postcode, venue_city 
			FROM '. VENUE_TABLE;
			// 		WHERE `venue_location_lat` = 0 OR `venue_location_lon` = 0 OR `venue_location_lat` IS NULL OR  `venue_location_lon` IS NULL';
			
			
	echo $sql .'<br /><br />';
	echo '<table border="3">
		  <tr>
				<td>ID</td>
				<td>Name</td>
				<td>Address</td>
				<td>Postcode</td>
				<td>City</td>
				<td>Result</td>
		   </tr>';
			
	 // Do the query
	$query = $conn->query($sql);
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) 
	{ 
		$query_for_Google = $row['venue_address'] .' '. $row['venue_postcode'].' '. $row['venue_city'];

		echo '<tr>
					<td>'. $row['venue_id'] .'</td>
					<td>'. $row['venue_name'] .'</td>
					<td>'. $row['venue_address'] .'</td>
					<td>'. $row['venue_postcode'].'</td>
					<td>'. $row['venue_city'] .'</td>';
	
		$GoogleGeocoding = null;
		
		// Required data?
		if ( !empty($row['venue_name'])  && !empty($row['venue_address']) && !empty($row['venue_postcode']) && strlen($row['venue_postcode']) > 4)
		{
			$GoogleGeocoding = geocode($query_for_Google);		
		}
			
				
		/* Returns:
		Array
		(
			[lat] => 37.7749295
			[lng] => -122.4194155
		)		
		*/

		// Success
		if ( is_array($GoogleGeocoding) )
		{
			echo '<td>Geocoding a success</td>';
				
			$_venue_location_lat = $GoogleGeocoding['lat'];
			$_venue_location_lon = $GoogleGeocoding['lng'];
			
			$_sql = 'UPDATE '. VENUE_TABLE .'
						SET 
						`venue_location_lat` = '. $_venue_location_lat .',
						`venue_location_lon` = '. $_venue_location_lon .',
						`venue_location_spatial_point` = GeomFromText(CONCAT(\'POINT (\', '. $_venue_location_lat  .', \' \', '. $_venue_location_lon .', \')\'))
						WHERE venue_id = '. $row['venue_id'];
											
			// Try to insert the new image stuff
			try
			{				
				$_query = $conn->exec($_sql);
			}
			catch(PDOException $e)
			{
				echo "Error. Failed to execute database query: " . $e->getMessage();
			}
		}
		else
		{			
				echo '<td>Geocoding FAILED</td>';
		}
		
		echo '</tr>';

		
		unset($_query);
		unset($_sql);
		
		
		// Sleep for .1 of a second to avoid being burnt by google
		time_nanosleep(0, 100000000);
			

	} // unset

	
	echo '</table></body></html>';
