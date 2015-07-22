<?php
/**********************************************************************
 *
 *	script file		: index.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: A quick page to show the map of all venues,
 *					  leverages JSON interface
 *
 **********************************************************************/
 
//
// Page initiation
//
define ('IN_APPLICATION', TRUE);
define ('DEBUG_MODE', FALSE);

include('include/common.php');
include('include/class_datespot.php'); // can't do much without this one

if (DEBUG_MODE)
{
	print_r($_REQUEST);
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Event & Venue Map</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
	
	<!-- CSS -->
	<link type="text/css" href="../static/bootstrap-3.3.4-dist/css/bootstrap.css" rel="stylesheet" />	
    <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
	
	<!-- JavaScript -->
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=false"></script>
	<script type="text/javascript" src="../static/jquery-2.1.4.min.js"> </script>	
	
   <script>
   
		var map;
   
		// Show the google map with defaults
		function initializeGoogleMap() {
		  var myLatlng = new google.maps.LatLng(51.462481,-0.169433); // Start at eckstein
		  var mapOptions = {
			zoom: 16,
			center: myLatlng
		  }
		  
		  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

 
		}
		
//	google.maps.event.addDomListener(window, 'load', initialize);
	
    </script>
	</head>

	<body onload="startPage()">
      <div class="alert alert-success" role="alert" id="position-success" style="display:none; margin:2px;"><strong>Hooorray!</strong> <span id="success-message"></span></div>
      <div class="alert alert-warning" role="alert" id="position-error" style="display:none; margin:2px;"><strong>Warning!</strong> <span id="error-message"></span></div>	 
	  
	
	
	<!-- Getting the Location and Initiation Stuff, need to have this here due to reliance on the DOM -->
	<!-- http://stackoverflow.com/questions/14028959/why-does-jquery-or-a-dom-method-such-as-getelementbyid-not-find-the-element -->
	<div id="map-canvas"></div>
	
	
	<script>

		var em, sm;
		
		em = document.getElementById("error-message");
		sm = document.getElementById("success-message");		

		/* Try and get the location */
		function startPage() 
		{
			
			/* Try and get the location */
			if (navigator.geolocation) 
			{
				navigator.geolocation.getCurrentPosition(showPosition, showError);		/* Call back function */
			} else { 
				$("#position-error").show();
				em.innerHTML = "Geolocation is not supported by this browser.";
			}
			
			// We love google
			initializeGoogleMap();
		}

		function showPosition(position) {
			sm.innerHTML = "We are centering the map to your current position. Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;	
			$("#position-success").show();
			
			var myOptions = {
                    zoom:2,
                    center: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    panControl: false
                };

			// If we have coordinates, center the map on these
			map.setCenter(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
			
			
			/* Build the marker 
			var contentString = '<div id="content">'+
			  '<div id="bodyContent">This is where you are SCROT!</p>'+
			  '</div>';

			var infowindow = new google.maps.InfoWindow({
			  content: contentString
			});
*/
			// Stick a marker at our position
			var marker = new google.maps.Marker({
			  position: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
			  map: map,
			  title: 'This is where you are SCROT!'
			});
			
			/*
		  google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		  });
		  
		  */
	  

			// OK. SO we know our position, show us local things.
			showVenuesAroundPoint(position.coords.latitude, position.coords.longitude);
		}
		
		/* Show all the venues */
		function showVenuesAroundPoint(lat, lng) 
		{

						
			// Make our call
			jQuery.ajax('./client.php', 
			{
				data:{
					originLat: lat,
					originLong: lng
				},
				dataType:'json'
			}).done(function(data) 
			{
				if(data.success) 
				{

					for(i in data.points) 
					{					
						var latLng = new google.maps.LatLng(data.points[i].Latitude, data.points[i].Longitude);
						
						var contentString = '<div id="content">'+
						  '<h5>' + data.points[i].Name + '</h5>'+
						  '<div id="bodyContent">'+
						  '<p>Latitude: ' + data.points[i].Latitude + '. Longitude: ' + data.points[i].Longitude + '</p>'+
						  '<p><b>Overall:</b> ' + data.points[i].GenRating + '&nbsp;&nbsp;&nbsp;<b>Cost:</b> ' + data.points[i].CostRating + '&nbsp;&nbsp;&nbsp;<b>Quirkiness:</b> ' + data.points[i].QuirkinessRating +'</p>'+
						  '<p>' + data.points[i].Description + '</p>'+					  
						  '</div>';
						  
						  
						var infowindow = new google.maps.InfoWindow({
						  content: contentString
						});						  

 
						// Creating a marker and putting it on the map
						var marker = new google.maps.Marker({
							position: latLng,
							map: map,
							title: "Distance Rank: "+ i + "\r\nName: " +data.points[i].Name,
							info: contentString // we need to use this or we get the same content for every bloody infomarket
						});
						
						// Thank god for: https://tommcfarlin.com/multiple-infowindows-google-maps/
						google.maps.event.addListener( marker, 'click', function() {
						 
						   infowindow.setContent( this.info );
						   infowindow.open( map, this );
						 
						});			

					 }
					
				} 
				
			});
			
			

			
		}
		

		function showError(error) {
			
			$("#position-error").show();
			
			switch(error.code) {
				case error.PERMISSION_DENIED:
					em.innerHTML = "User denied the request for Geolocation."
					break;
				case error.POSITION_UNAVAILABLE:
					em.innerHTML = "Location information is unavailable."
					break;
				case error.TIMEOUT:
					em.innerHTML = "The request to get user location timed out."
					break;
				case error.UNKNOWN_ERROR:
					em.innerHTML = "An unknown error occurred."
					break;
			}
		}
		
	</script>
		   


  </body>
</html>

