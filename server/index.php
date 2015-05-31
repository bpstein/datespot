<?php
/**********************************************************************
 *
 *	script file		: index.php
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
include('include/class_datespot.php'); // can't do much without this one


///
// Some Constants
//

$venue_scenarios = array
(
	'firstdate' 	=> 'First Date',
	'seconddate'	=> 'Second Date',
	'thirddate'		=> 'Third Date',	
	'drinksonly'	=> 'Drinks Only',
	'dinnerdate'	=> 'Dinner Date',
	'sealthedeal'	=> 'Seal the Deal',
	'goallout'		=> 'Go All Out'
);

if (DEBUG_MODE)
{
	print_r($_REQUEST);
}

//
// Processing?
//

if ( @$_REQUEST['action'] == 'submit_edit_venue' )
{

	// Message
	$success_msg = '';
	
	// Prep the SQL variables
	$_venue_scenario 	= implode(',', $_REQUEST['venue_scenario']);
	$_venue_open_sunday = isset($_REQUEST['venue_open_sunday']) ? 'Y':'N';
	$_venue_open_monday = isset($_REQUEST['venue_open_sunday']) ? 'Y':'N';	
	
	$_venue_rating_general	= !is_numeric($_REQUEST['venue_rating_general']) ? 0:$_REQUEST['venue_rating_general'];
	$_venue_rating_cost		= !is_numeric($_REQUEST['venue_rating_cost']) ? 0:$_REQUEST['venue_rating_cost'];	
	
	if ( @is_numeric ($_REQUEST['venue_id']))
	{
		
		// Prime the message so far
		$success_msg = 'Updated database successfully with: '. clean_string($_REQUEST['venue_name']);	

	
		$sql = 'UPDATE '. VENUE_TABLE .' 
					SET `venue_modified`=NOW(),
					`venue_name`		=\''. clean_string($_REQUEST['venue_name']) .'\',	
					`venue_description`	=\''. clean_string($_REQUEST['venue_description']).'\',
					`venue_postcode`	=\''. clean_string($_REQUEST['venue_postcode']).'\',
					`venue_address`		=\''. clean_string($_REQUEST['venue_address']).'\',
					`venue_rating_general`	=\''. $_venue_rating_general .'\',
					`venue_rating_cost`		=\''. $_venue_rating_cost .'\',
					`venue_scenario`		=\''. clean_string($_venue_scenario) .'\',
					`venue_url`				=\''. clean_string($_REQUEST['venue_url']).'\',
					`venue_open_sunday`		= \''. $_venue_open_sunday .'\',
					`venue_open_monday`		= \''. $_venue_open_monday .'\',				
					`venue_hour_open`		=\''. clean_string($_REQUEST['venue_hour_open']) .'\',
					`venue_hour_close`		=\''. clean_string($_REQUEST['venue_hour_close']) .'\'
				WHERE venue_id = '. $_REQUEST['venue_id'];
	}
	else
	{
		
		// Prime the message so far
		$success_msg = 'Successfully created venue: '. clean_string($_REQUEST['venue_name']);	
		
		// New Venue!!
		$sql = 'INSERT INTO '. VENUE_TABLE .' 
			(	   `venue_created`, `venue_modified`, `venue_unique_id`, 
				   
				   `venue_name`, 
				   `venue_description`, 
				   `venue_postcode`, 
				   `venue_address`, 
				   `venue_rating_general`, 
				   `venue_rating_cost`, 
				   `venue_scenario`, 
				   `venue_url`, 
				   `venue_open_sunday`, 
				   `venue_open_monday`, 
				   `venue_hour_open`, 
				   `venue_hour_close`
			   ) 
			   VALUES 
			   (NOW(), NOW(), MD5(NOW()),
				\''. clean_string($_REQUEST['venue_name']) .'\',	
				\''. clean_string($_REQUEST['venue_description']).'\',
				\''. clean_string($_REQUEST['venue_postcode']).'\',
				\''. clean_string($_REQUEST['venue_address']).'\',
				\''. $_venue_rating_general .'\',
				\''. $_venue_rating_cost .'\',
				\''. clean_string($_venue_scenario) .'\',
				\''. clean_string($_REQUEST['venue_url']).'\',
				\''. $_venue_open_sunday .'\',
				\''. $_venue_open_monday .'\',				
				\''. clean_string($_REQUEST['venue_hour_open']) .'\',
				\''. clean_string($_REQUEST['venue_hour_close']) .'\')';
	}
	
				
	// Do the database routine
	try
	{				
		$query = $conn->exec($sql);
	}
	catch(PDOException $e)
	{
		$failure_msg = "Error. Failed to execute database query: " . $e->getMessage();
		$success_msg = '';	
	}
	
	$_REQUEST['action'] = '';
	
}






//
// Just so we avoid errors
//

if  (!isset($_REQUEST['action']) || empty($_REQUEST['action']) )
{
	$_REQUEST['action'] = 'venuelist';
}






?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>DateSpot Administration ...</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <link rel="icon" href="static/favicon-16.png" type="image/png" />	
	
	<!-- Style Sheets -->
    <link type="text/css" href="static/css/template.css" rel="stylesheet" />

	<!-- JQuery CSS -->
	<link type="text/css" href="static/jquery-ui-1.11.4/jquery-ui.css" rel="stylesheet" />	
	
	<!-- Bootstrap CSS -->
	<link type="text/css" href="static/bootstrap-3.3.4-dist/css/bootstrap.css" rel="stylesheet" />
	
	<!-- Dyntable CSS -->
	<link type="text/css" href="static/jspkg-dynatable/jquery.dynatable.css" rel="stylesheet" />


	
	
	<!-- Leafelet -->
	<link rel="stylesheet" href="static/leaflet/leaflet.css" />
	<script src="static/leaflet/leaflet.js"></script>
	
	<style type="text/css">
	#map {
		height: 100%;
		width: 100%;
	}
	</style>
<script>
	var map, markerLayer;

	function findPoint(e) {
		// Prevent the query from scanning too wide an area
		if(map.getZoom() < 14) {
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
		jQuery.ajax('client.php', {
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
					.bindPopup('Latitude: '+e.latlng.lat+'<br />Longitude: '+e.latlng.lng);
	
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

	function initializeMap() {
		// Create a map object (roughly positioned over Eckstein Baby)
		map = L.map('map').setView([51.46186,-0.16826], 16);

		// Select the tiles
		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		    minZoom: 10,
		    maxZoom: 20
		}).addTo(map);

		// Bind our click function to the map
		map.on('click', findPoint);
	}

	</script>
	
	
	<!-- JavaScript - Jquery -->
	<script type="text/javascript" src="static/jquery-2.1.4.min.js"> </script>
	
	<!-- JavaScript - Jquery UI -->	
	<script type="text/javascript" src="static/jquery-ui-1.11.4/jquery-ui.js"> </script>
	
	<!-- JavaScript - Dynatable -->		
	<script type="text/javascript" src="static/jspkg-dynatable/jquery.dynatable.js"> </script>
	
	<!-- Bootstrap -->
	<script type="text/javascript" src="static/bootstrap-3.3.4-dist/js/bootstrap.min.js"> </script>	
		
	
	
	<!-- JQUery Content Open -->
	<script type="text/javascript">
	$(function(){
	
	  // Tabbed Interface
	  $( "#tabs" ).tabs(); 

	  //Dynatable
	  $('#my-venue-table').dynatable();
		
	  // Json Test Button
	  $( "#jsontest-button" )
		  .button()
		  .click(function( event ) {
			  
		  $.getJSON( "http://192.168.0.9/client.php", {
			a: "query",
			tagmode: "any", 	/* not used */
			format: "json" 		/* not used */
		  })
			.done(function( data ) 
			{
				
			 alert(data);
	
			  
		
			}); // end done
			
			alert('Finished attempted JSON query');



		  });		

		
	});
	</script>			
    </head>
  <body  onload="initializeMap()">  
  
 
 


      <!-- Static navbar -->
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">DateSpot</a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li <?php echo ((@$_REQUEST['action'] == 'venuelist') ? '':'class="active"'); ?>><a href="?action=">Venue List</a></li>
              <li <?php echo ((@$_REQUEST['action'] == 'map') ? '':'class="active"'); ?>><a href="?action=map">Venue Map</a></li>
              <li <?php echo ((@$_REQUEST['action'] == 'users') ? '':'class="active"'); ?>><a href="?action=users">User List</a></li>
			  <li <?php echo ((@$_REQUEST['action'] == 'log') ? '':'class="active"'); ?>><a href="?action=log">Logging</a></li>	
			  <li <?php echo ((@$_REQUEST['action'] == 'jsontest') ? '':'class="active"'); ?>><a href="?action=jsontest">JSON Query Test</a></li>			  
			  <!--
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li class="dropdown-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>
			  -->
            </ul>
			
		<ul class="nav navbar-nav navbar-right">
              <li><a href="" data-toggle="modal" data-target="#myModal">Version 0.00001</a></li>
            </ul>			
           
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
      </nav>
	  
	  
	  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Yes, it's only the beginning, scrot.</h4>
        </div>
        <div class="modal-body">
          <p>Lots of bugs and things needs to be fixed with this.</p>
		  <ul><li>Geo-Cordinates</li><li>Venue Image Upload</li><li>JSON to actually work</li><li>Events?</li><li>... and heaps of others to list here</li></ul>
		  
		  There's enough to allow you to start filling this place up with venues though.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
	
	
<?php

	if ( !empty($success_msg))
	{
		echo '<div class="alert alert-success" role="alert"><strong>Well done!</strong> '. $success_msg .'.</div>';		
	}
	
	if ( !empty($failure_msg))
	{
		echo '<div class="alert alert-danger" role="alert"><strong>Error!</strong> '. $failure_message.' .</div>';		
	}	

	
	
	
	

	/**********************************************************
	 *
	 * PHP ACTION SECTION START - LIST VENUES
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'venuelist' ) 
	{

?>

  
  <div class="contentbox">
	
	<!-- Start: Create new Venue -->
	<p>
	<a href="?action=edit_venue">
		<button type="button" class="btn btn-default btn-primary" aria-label="Create New Venue">
		<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create New Venue
		</button>
	</a>
	</p>
	<!-- End: Create new Venue -->
	
  <p>
  
	  <!-- Venue Listing -->
	  <table id="my-venue-table" class="table table-bordered">
	  <thead>
		<tr>
		  <th class="dyntable-head">ID</th>
		  <th class="dyntable-head">Name</th>
		  <th class="dyntable-head">Description</th>
		  <th class="dyntable-head">Postcode</th>
		  <th class="dyntable-head">Address</th>
		  <th class="dyntable-head">Rating</th>
		  <th class="dyntable-head">Price</th>
		  <th class="dyntable-head">Scenario</th>
		</tr>
	  </thead>
	  <tbody>
<?php

	foreach (DateSpot::get_venue() AS $venue)
	{
?>		
		
		<tr>
		  <td><?php echo $venue['venue_id']; ?></td>
		  <td><a href="?action=edit_venue&venue_id=<?php echo $venue['venue_id']; ?>"><?php echo $venue['venue_name']; ?></a></td>
		  <td><?php echo $venue['venue_description']; ?></td>
		  <td><?php echo $venue['venue_postcode']; ?></td>
		  <td><?php echo $venue['venue_address']; ?></td>
		  <td><?php echo $venue['venue_rating_general']; ?></td>
		  <td><?php echo $venue['venue_rating_cost']; ?></td>
		  <td><?php echo $venue['venue_scenario']; ?></td>	  
		</tr>
<?php

	}
?>			
	  </tbody>
	</table>
</p>
  </div>
  
<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}



	/**********************************************************
	 *
	 * PHP ACTION SECTION START - EDIT / CREATE VENUE
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'edit_venue' ) 
	{
		
		$data = array();
		
		$actiontitle = 'Create Venue';
		
		if (isset($_REQUEST['venue_id']))
		{
			if ( is_numeric ($_REQUEST['venue_id']))
			{
					$data = DateSpot::get_venue($_REQUEST['venue_id']);
					$data = $data[0];
					
					$actiontitle = 'Edit Venue';
			}
		}
		
	///	print_r($data);

?>

  <div class="contentbox">
  
  <h3 style="border-bottom: thin solid #ccc; padding:10px;"><?php echo $actiontitle; ?></h3>

  <h4 style="border-bottom: thin dotted #ccc; padding:10px;">Basic Venue Information</h4>
  <form class="form-horizontal" role="form" method="post">
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_name">Name:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="venue_name" id="venue_name" value="<?php echo @$data['venue_name']; ?>">
      </div>
    </div>
	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_description">Description:</label>
      <div class="col-sm-10">	  
	<textarea class="form-control" rows="5" name="venue_description" id="venue_description"><?php echo @$data['venue_description']; ?></textarea>
      </div>
    </div>
	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_country">Country:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="venue_country" id="venue_country" value="<?php echo @$data['venue_country']; ?>" disabled>
		Not currently in use. Defaults to 'UK'.
      </div>
    </div>	
	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_country">City:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="venue_city" id="venue_city" value="<?php echo @$data['venue_city']; ?>" disabled>
		Not currently in use. Defaults to 'London'.
      </div>
    </div>

    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_postcode">Postcode:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" name="venue_postcode" id="venue_postcode" value="<?php echo @$data['venue_postcode']; ?>">
      </div>
    </div>	

    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Address:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" name="venue_address" id="venue_address" value="<?php echo @$data['venue_address']; ?>" />
      </div>
    </div>	
	
   <div class="form-group">
      <label class="control-label col-sm-2" for="venue_url">URL:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" name="venue_url" id="venue_url" value="<?php echo @$data['venue_url']; ?>">
		The http:// web address of the venue's website.
      </div>
    </div>		
	
	
	
	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Operating Days:</label>
      <div class="col-sm-10">
		
		<div data-toggle="buttons">
		
<?php

			/* 
			    [venue_open_sunday] => Y
				[venue_open_monday] => Y
				[venue_hour_open] => 10:00:00
				[venue_hour_close] => 00:00:00
			*/

			// Open Sunday
			$_checked = '';
			$_active  = '';
			if ( @$data['venue_open_sunday'] == 'Y' ) { $_checked = ' checked'; $_active = ' active';  } 
			
			echo '<label class="btn btn-default'. $_active .'"><input name="venue_open_sunday" value="Y" type="checkbox" autocomplete="off"'. $_checked .">Venue Open Sundays</label>\r\n\r\n";
			
			
			// Open Monday
			$_checked = '';
			$_active  = '';
			if ( @$data['venue_open_monday'] == 'Y' ) { $_checked = ' checked'; $_active = ' active';  } 
			
			echo '<label class="btn btn-default'. $_active .'"><input name="venue_open_sunday" value="Y" type="checkbox" autocomplete="off"'. $_checked .">Venue Open Sundays</label>\r\n\r\n";
		
?>
		
		</div>
		Tuesday to Saturday are assumed as standard operating days.
      </div>
    </div>	
	
	

	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Standard Hours:</label>
      
	  <div class="col-sm-2"><input type="text" class="form-control" name="venue_hour_open" id="venue_hour_open" value="<?php echo @$data['venue_hour_open']; ?>"> Opening (HH:MM:SS)</div> 
	  <div class="col-sm-2"><input type="text" class="form-control" name="venue_hour_close" id="venue_hour_close" value="<?php echo @$data['venue_hour_close']; ?>"> Closing (HH:MM:SS)</div>
    </div>		



	
	
	
	
	<!-- Datespot Venue Attributes -->
	<h4 style="border-bottom: thin dotted #ccc; padding:10px;">DateSpot Venue Attributes</h4>
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_rating_general">General Rating:</label>
      <div class="col-sm-1">
        <input type="text" class="form-control" name="venue_rating_general" id="venue_rating_general" value="<?php echo @$data['venue_rating_general']; ?>">
      </div>
	  		A number from 1 to 10 of the 'awesomeness' of a venue. 10 being fantastic. Subjective.
    </div>
	
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_rating_cost">Price Rating:</label>
      <div class="col-sm-1">
        <input type="text" class="form-control" name="venue_rating_cost" id="venue_rating_cost" value="<?php echo @$data['venue_rating_cost']; ?>">
      </div>
	  		A number from 1 to 10 of the 'price' of a venue. 1 being inexpensive, 10 being break the bank. Subjective.
    </div>	

    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Scenario:</label>
      <div class="col-sm-10">
		
		<div data-toggle="buttons">
		
<?php
			// What was selected for this event?
			$selected_scenarios = array();
			if ( !empty($data['venue_scenario']) )
			{
				$selected_scenarios = explode(',', $data['venue_scenario']);
				
				//echo 'Selected Scenarios:';
				//print_r ($selected_scenarios);
	
			}
				
			foreach ($venue_scenarios AS $key => $description)
			{
				$_checked = '';
				$_active  = '';
				if ( in_array($key, $selected_scenarios) ) { $_checked = ' checked'; $_active = ' active';  } 
				
				echo '<label class="btn btn-default'. $_active .'"><input name="venue_scenario[]" value="'.$key.'" type="checkbox" autocomplete="off"'. $_checked .'> '. $description ."</label>\r\n\r\n";
				
			}
			
?>
		
		</div>



      </div>
    </div>	


	
	

    <div class="form-group">        
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Save to database!</button>
      </div>
    </div>
	
	<input type="hidden" name="venue_id" value="<?php echo @$data['venue_id']; ?>" />
	<input type="hidden" name="venue_unique_id" value="<?php echo @$data['venue_unique_id']; ?>" />
	<input type="hidden" name="action" value="submit_edit_venue" />
	
	
<?php 

	// database bullocks
	if ( !empty($data))
	{
?>		
	
	<!-- Internal Attributes -->
	<h4 style="border-bottom: thin dotted #ccc; padding:10px;">Internal Database Bullocks</h4>
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_created">Created:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" id="venue_created" value="<?php echo @$data['venue_created']; ?>" disabled>
      </div>
    </div>
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_modified">Last Modified:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" id="venue_modified" value="<?php echo @$data['venue_modified']; ?>" disabled>
      </div>
    </div>	
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_unique_id">UniqueID:</label>
      <div class="col-sm-5">
        <input type="text" class="form-control" id="venue_unique_id" value="<?php echo @$data['venue_unique_id']; ?>" disabled>
      </div>
    </div>	

<?php
	
	} // database bullocks
	
?>

	
  </form>
 
</div>


<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}
	
	

	/**********************************************************
	 *
	 * PHP ACTION SECTION START
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'map' ) 
	{

?>

  <div class="contentbox">
    <p><div id="map"></div></p>

  </div>
  
   
<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}

?>    
  
  
  

  
<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION START
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'users' ) 
	{

?>

    <div class="contentbox">
    <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
    <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
  </div>
  

  
  <?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}

?>    
  
  
  
  
 
<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION START
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'log' ) 
	{

?>

    <div class="contentbox">
    <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
    <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
  </div>
  

  
  <?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}

?>    
  
    
  
  
  
<?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION START
	 *
	 **********************************************************/
	if ( $_REQUEST['action'] == 'jsontest' ) 
	{

?>
  
  
  
  
    <div class="contentbox">
	
    <p>
	<div style="padding-top:10px; padding-bottom:10px;"><button id="jsontest-button">JSON Query</button></div>
	
	<form>
		<textarea style="width:100%" id="jsontest-textarea">Click 'JSON Query' to make a call to make an AJAX JSON call to 'client.php's and return the result.</textarea>
	</form>
	
	</p>	
  </div>
  
  
  
  <?php

	/**********************************************************
	 *
	 * PHP ACTION SECTION END
	 *
	 **********************************************************/
	 
	}

?>    
  
		
	
		
		
  </body>
</html>
