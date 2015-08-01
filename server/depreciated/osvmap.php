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
	
	<!-- CSS -->
	<link type="text/css" href="../static/bootstrap-3.3.4-dist/css/bootstrap.css" rel="stylesheet" />	
	<link type="text/css" href="../static/leaflet/leaflet.css" rel="stylesheet" />
	
	<!-- JavaScript -->
	<script type="text/javascript" src="../static/jquery-2.1.4.min.js"> </script>
	<script type="text/javascript" src="../static/leaflet/leaflet.js"></script>
	<script type="text/javascript" src="../static/bootstrap-3.3.4-dist/js/bootstrap.min.js"> </script>		

	<!-- Map Style -->		
	<style type="text/css">
	#map {
		height: 85%;
		width: 100%
	}
	</style>
	
	<!-- Displaying the Map and Venues Stuff -->
	<script>
	
		var map, markerLayer;
	 /*
		function findPoint(e) {
			// Prevent the query from scanning too wide an area
			if(map.getZoom() > 14) {
				var popup = L.popup()
				.setLatLng(e.latlng)
				.setContent('Please zoom before searching for postcodes')
				.openOn(map);
				return;
			}
			 
			// Find the boundary of the map (to minimise our db search)
			bounds = map.getBounds();
	 
			// Find the clicked position
			pos = e.latlng;
	 
			// Make our call
			jQuery.ajax('http://www.tec20.co.uk/tutorials/nearestPostcodes/nearestPostcodesJson.php', {
				data:{
					minLat: bounds._northEast.lat,
					minLong: bounds._northEast.lng,
					maxLat: bounds._southWest.lat,
					maxLong: bounds._southWest.lng,
					originLat: pos.lat,
					originLong: pos.lng
				},
				dataType:'json'
			}).done(function(data) {
				if(data.success) {
					// If we already have a marker layer, remove it
					if(!!markerLayer)
						map.removeLayer(markerLayer);
		 
					// Create a new marker layer
					markerLayer = new L.LayerGroup();
					for(i in data.points) {
						// Add markers for each of the points returned
						new L.marker([data.points[i].Latitude, data.points[i].Longitude])
							.addTo(markerLayer)
							.bindPopup(data.points[i].Name);
					}
		 
					// Add the point where we clicked on the map
					new L.marker([e.latlng.lat, e.latlng.lng])
						.addTo(markerLayer)
						.bindPopup('Latitude: '+e.latlng.lat+'&lt;br /&gt;Longitude: '+e.latlng.lng);
		 
					// Add the layer to the map
					markerLayer.addTo(map);
				} else {
					// The JSON request failed
					var popup = L.popup()
					.setLatLng(e.latlng)
					.setContent(data.error)
					.openOn(map);
				}
			});
		}
		*/
		
		/* Show all the venues */
		function showVenuesAroundPoint(lat, lng) 
		{
			
			/* Set the map to the current coordinates we have */
			map.setView([lat, lng], 16);	
			
			// Create a new marker layer
			
			markerLayer2 = new L.LayerGroup();			
			
			// Add the point where we exist on the map
			new L.marker([lat, lng])
				.addTo(markerLayer2)
				.bindPopup('This is where you currently are apparently you SCROT');
				
			// Add the layer to the map
			markerLayer2.addTo(map);					

			
		
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

				// If we already have a marker layer, remove it
				if(!!markerLayer)
					map.removeLayer(markerLayer);

				// Create a new marker layer
				markerLayer = new L.LayerGroup();
			
				for(i in data.points) 
				{					
					// Add markers for each of the points returned

					new L.marker({lat: data.points[i].Latitude, lng: data.points[i].Longitude})
						.addTo(markerLayer2)
						.bindPopup(i + " " +data.points[i].Name);
		
				 }
					
					
				// Add the layer to the map
				markerLayer2.addTo(map);	
		 
				} 
				else 
				{
					// The JSON request failed
					var popup = L.popup()
					.setContent(data.error)
					.openOn(map);
				}
			});
			
			

			
		}

		
		
	 
		function initializeMap() {
			
			// Create a map object (roughly positioned over Birmingham by default)
			map = L.map('map').setView([52.479,-1.896], 16);
	 
			// Select the tiles
			L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				attribution: 'Map data &copy; OpenStreetMap. DateSpot&trade; Grant Bartlett and Ben Stein',
				minZoom: 10,
				maxZoom: 20
			}).addTo(map);
		
		}
		
	</script>
	
	
	</head>

	<body onload="startPage()">
	
      <div class="alert alert-success" role="alert" id="position-success" style="display:none;">
        <strong>Hooorray!</strong> <span id="success-message"></span>
      </div>
	  
	  
      <div class="alert alert-warning" role="alert" id="position-error" style="display:none;">
        <strong>Warning!</strong> <span id="error-message"></span>
      </div>	  
	
    <div id="map"></div>
	
	
	<!-- Getting the Location and Initiation Stuff, need to have this here due to reliance on the DOM -->
	<!-- http://stackoverflow.com/questions/14028959/why-does-jquery-or-a-dom-method-such-as-getelementbyid-not-find-the-element -->
	<script>
	
		/* We'll replace the contents of this if we can't get it to work */
		var em, sm;
		
		em = document.getElementById("error-message");
		sm = document.getElementById("success-message");		

		/* Try and get the location */
		function startPage() 
		{

			/* Initialize the Leaflet Map anyway */
			initializeMap();
			
			/* Try and get the location */
			if (navigator.geolocation) 
			{
				navigator.geolocation.getCurrentPosition(showPosition, showError);		/* Call back function */
			} else { 
				$("#position-error").show();
				em.innerHTML = "Geolocation is not supported by this browser.";
			}
		}

		function showPosition(position) {
			sm.innerHTML = "We are centering the map to your current position. Latitude: " + position.coords.latitude + " Longitude: " + position.coords.longitude;	
			$("#position-success").show();
	
			/* Show the map position and nearest venues */
			showVenuesAroundPoint(position.coords.latitude, position.coords.longitude);
	
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


	<style>

	.highlight {

	  padding: 9px 14px;
	  margin-bottom: 14px;
	  margin-top: 14px;  
	  background-color: #fff;
	  border: 1px solid #e1e1e8;
	  border-radius: 4px;
	}

	</style>
<!--


	<div class="highlight">

<h4>Ordered by Proximity</h4>
 <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Address</th>		  
          <th>Postcode</th>
          <th>Latitude</th>
          <th>Longitude</th>		  
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row">1</th>
          <td>Mark</td>
          <td>Otto</td>
          <td>@mdo</td>
        </tr>
        <tr>
          <th scope="row">2</th>
          <td>Jacob</td>
          <td>Thornton</td>
          <td>@fat</td>
        </tr>
        <tr>
          <th scope="row">3</th>
          <td>Larry</td>
          <td>the Bird</td>
          <td>@twitter</td>
        </tr>
      </tbody>
    </table>

	
</div>
-->
  </body>
</html>

