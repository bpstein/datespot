<?php
/**********************************************************************
 *
 *	script file		: geocoding.php
 *	
 *	begin			: 
 *	copyright		: 
 *  descriptions	: For the geocoding of addresses using the 
 *					  Google geocoding library
 *
 **********************************************************************/
 
 function geocode($string)
 {
 
   // My Google API Key is: AIzaSyDQI_m8dnr-JL6jeZg0MNZ4J2DrZplxOn0
   
   $string = str_replace (" ", "+", urlencode($string));
   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
 
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
	   
    return false;
	
   }
 
   //print_r($response);
   $geometry = $response['results'][0]['geometry'];
 
   return $geometry['location'];
    
 
}
/*
	$city = 'San Francisco, USA';
	 
	$array = geocode($city);
	print_r($array);
 */
 
 
 /* Returns:
 
	 
	Array
	(
		[lat] => 37.7749295
		[lng] => -122.4194155
	)
	
  */
 
 