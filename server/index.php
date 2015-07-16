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

if (DEBUG_MODE)
{
	print_r($_REQUEST);
}


///
// Some Constants
//

$venue_scenarios = array
(
	'firstdate' 	=> 'First Date',	
	'drinksonly'	=> 'Drinks Date',
	'dinnerdate'	=> 'Dinner Date',
	'activedate'	=> 'Active Date (do something fun)', 	// new
	'visitor'		=> 'Visitor to Town', 					// new
	'friends'		=> 'Drinks with Friends', 				// new
	
	'sealthedeal'	=> 'Seal the Deal',
	'goallout'		=> 'Go All Out',
	'brunch'	=> 'Brunch'

	
);

// Seem stupid that I have to declare this
$days_in_a_week = array ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	

//
// Processing?
//

if ( @$_REQUEST['action'] == 'submit_edit_venue' )
{

	// Message
	$success_msg = '';
	$failure_message = '';
	
	// Prep the SQL variables
	$_venue_scenario 	= implode(',', $_REQUEST['venue_scenario']);
	
	/******** OLD LOGIC 
	$_venue_open_sunday = isset($_REQUEST['venue_open_sunday']) ? 'Y':'N';
	$_venue_open_monday = isset($_REQUEST['venue_open_monday']) ? 'Y':'N';
	*/
	
	
	// New Logic just so the database is OK
	if ( isset($_REQUEST['venue_hour_sunday_open']) &&  isset($_REQUEST['venue_hour_sunday_close'])  )
	{
		if ( $_REQUEST['venue_hour_sunday_open'] == $_REQUEST['venue_hour_sunday_close'])
		{
			$_venue_open_sunday = 'N';
		}
		else
		{
			$_venue_open_sunday = 'Y';
		}
	}
	
	if ( isset($_REQUEST['venue_hour_monday_open']) &&  isset($_REQUEST['venue_hour_monday_close'])  )
	{
		if ( $_REQUEST['venue_hour_monday_open'] == $_REQUEST['venue_hour_monday_close'])
		{
			$_venue_open_monday = 'N';
		}
		else
		{
			$_venue_open_monday = 'Y';
		}
	}
	
	
	/** 
	 * Handling of in-frequent but ongoing venues and events:
	 *
	 * In this section we do the validation and calculation of relative repeating dates (ie. First Monday of Next Month), 
	 * and store this in a calculated column in MySQL for later use 
	 */
	$begin = new DateTime( ); // Today's date as the starting ppoint
	
	$_venue_date_operating_frequency_1 = 'NULL';
	$_venue_date_operating_frequency_2 = 'NULL';
	$_venue_date_operating_frequency_1_next_calculated = 'NULL';
	$_venue_date_operating_frequency_2_next_calculated = 'NULL';
	if ( isset($_REQUEST['venue_date_operating_frequency_1'] ) )
	{
		$_venue_date_operating_frequency_1 = clean_string(trim($_REQUEST['venue_date_operating_frequency_1']));
		
		// Must have been garbage, or rubbish characters 
		if ( !empty($_venue_date_operating_frequency_1))
		{
			// Calculate the interval
			$interval = DateInterval::createFromDateString($_venue_date_operating_frequency_1);
			$period = new DatePeriod($begin, $interval, 1, DatePeriod::EXCLUDE_START_DATE); // next interval only
			
			// This should only iterate once
			foreach ( $period as $dt ){
			  $_venue_date_operating_frequency_1_next_calculated = '\''. $dt->format( "Y-m-d" ) .'\''; // MySQL format: 2015-08-12
			}	
			
			// Show this all the time for the time being
			if (true) { debug_message('Next calculated date of operation for this venue: '. $_venue_date_operating_frequency_1_next_calculated); }
			
		} // end calc of frquency 1
	} // end freq 1
	
	
	// DateSpot Rating
	$_venue_rating_general		= !is_numeric($_REQUEST['venue_rating_general']) ? 0:$_REQUEST['venue_rating_general'];
	$_venue_rating_quirkiness	= !is_numeric($_REQUEST['venue_rating_quirkiness']) ? 0:$_REQUEST['venue_rating_quirkiness'];	
	$_venue_rating_cost			= !is_numeric($_REQUEST['venue_rating_cost']) ? 0:$_REQUEST['venue_rating_cost'];	
	
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
					`venue_rating_quirkiness`	=\''. $_venue_rating_quirkiness .'\', 
					`venue_rating_cost`		=\''. $_venue_rating_cost .'\',
					`venue_scenario`		=\''. clean_string($_venue_scenario) .'\',
					`venue_url`				=\''. clean_string($_REQUEST['venue_url']).'\',
					`venue_open_sunday`		= \''. $_venue_open_sunday .'\',
					`venue_open_monday`		= \''. $_venue_open_monday .'\',				
					`venue_hour_open`		=\''. clean_string($_REQUEST['venue_hour_open']) .'\',
					`venue_hour_close`		=\''. clean_string($_REQUEST['venue_hour_close']) .'\',
					`venue_hour_monday_open`		=\''. clean_string($_REQUEST['venue_hour_monday_open']) .'\',
					`venue_hour_monday_close` 		=\''. clean_string($_REQUEST['venue_hour_monday_close']) .'\',
					`venue_hour_tuesday_open` 		=\''. clean_string($_REQUEST['venue_hour_tuesday_open']) .'\',
					`venue_hour_tuesday_close` 		=\''. clean_string($_REQUEST['venue_hour_tuesday_close']) .'\',
					`venue_hour_wednesday_open` 	=\''. clean_string($_REQUEST['venue_hour_wednesday_open']) .'\',
					`venue_hour_wednesday_close`	=\''. clean_string($_REQUEST['venue_hour_wednesday_close']) .'\',
					`venue_hour_thursday_open` 		=\''. clean_string($_REQUEST['venue_hour_thursday_open']) .'\',
					`venue_hour_thursday_close` 	=\''. clean_string($_REQUEST['venue_hour_thursday_close']) .'\',
					`venue_hour_friday_open`		=\''. clean_string($_REQUEST['venue_hour_friday_open']) .'\',
					`venue_hour_friday_close` 		=\''. clean_string($_REQUEST['venue_hour_friday_close']) .'\',
					`venue_hour_saturday_open` 		=\''. clean_string($_REQUEST['venue_hour_saturday_open']) .'\',
					`venue_hour_saturday_close` 	=\''. clean_string($_REQUEST['venue_hour_saturday_close']) .'\',
					`venue_hour_sunday_open`		=\''. clean_string($_REQUEST['venue_hour_sunday_open']) .'\',
					`venue_hour_sunday_close`		=\''. clean_string($_REQUEST['venue_hour_sunday_close']) .'\',
					`venue_date_open`		=\''. clean_string($_REQUEST['venue_date_open']) .'\',					
					`venue_date_close`		=\''. clean_string($_REQUEST['venue_date_close']) .'\',
					
					`venue_date_operating_frequency_1`					= \''. $_venue_date_operating_frequency_1 .'\',
					`venue_date_operating_frequency_1_next_calculated`	= '. $_venue_date_operating_frequency_1_next_calculated .'
					
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
					`venue_rating_quirkiness`,
					`venue_rating_cost`, 
					
					`venue_scenario`, 
					`venue_url`, 
					`venue_open_sunday`, 
					`venue_open_monday`, 
					`venue_hour_open`, 
					`venue_hour_close`,
					
					`venue_hour_monday_open`,
					`venue_hour_monday_close` ,
					`venue_hour_tuesday_open` ,
					`venue_hour_tuesday_close` ,
					`venue_hour_wednesday_open` ,
					`venue_hour_wednesday_close`,
					`venue_hour_thursday_open` ,
					`venue_hour_thursday_close` ,
					`venue_hour_friday_open`,
					`venue_hour_friday_close`, 
					`venue_hour_saturday_open`, 
					`venue_hour_saturday_close`, 
					`venue_hour_sunday_open`,
					`venue_hour_sunday_close`,
					
					`venue_date_open`,
					`venue_date_close`,
					
										
					`venue_date_operating_frequency_1`,
					`venue_date_operating_frequency_1_next_calculated`
					

			   ) 
			   VALUES 
			   (NOW(), NOW(), MD5(NOW()),
				\''. clean_string($_REQUEST['venue_name']) .'\',	
				\''. clean_string($_REQUEST['venue_description']).'\',
				\''. clean_string($_REQUEST['venue_postcode']).'\',
				\''. clean_string($_REQUEST['venue_address']).'\',
				
				\''. $_venue_rating_general .'\',
				\''. $_venue_rating_quirkiness .'\',
				\''. $_venue_rating_cost .'\',
				
				\''. clean_string($_venue_scenario) .'\',
				\''. clean_string($_REQUEST['venue_url']).'\',
				\''. $_venue_open_sunday .'\',
				\''. $_venue_open_monday .'\',				
				\''. clean_string($_REQUEST['venue_hour_open']) .'\',
				\''. clean_string($_REQUEST['venue_hour_close']) .'\',

				\''. clean_string($_REQUEST['venue_hour_monday_open']) .'\',
				\''. clean_string($_REQUEST['venue_hour_monday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_tuesday_open'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_tuesday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_wednesday_open'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_wednesday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_thursday_open'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_thursday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_friday_open'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_friday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_saturday_open'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_saturday_close'])  .'\',
				\''. clean_string($_REQUEST['venue_hour_sunday_open'])  .'\',				
				\''. clean_string($_REQUEST['venue_hour_sunday_close'])  .'\',

				\''. clean_string($_REQUEST['venue_date_open'])  .'\',				
				\''. clean_string($_REQUEST['venue_date_close'])  .'\',

					
				\''. $_venue_date_operating_frequency_1 .'\',
				'. $_venue_date_operating_frequency_1_next_calculated .'
	
				)';
	}
	
	if (DEBUG_MODE) debug_message($sql);
	
				
	// Do the database routine
	$query_success = true;
	try
	{				
		$query = $conn->exec($sql);
	}
	catch(PDOException $e)
	{
		$failure_msg = "Error. Failed to execute database query: " . $e->getMessage();
		$success_msg = '';

		$query_success = false;		
	}
	
	
	/*
	 * Venue Geocoding and Location Table Updates, and Date Frequency Stuff
	 */
	if ( $query_success )
	{
		if (DEBUG_MODE) { debug_message('The event table update query was as success. Performing Geocoding.'); }
	
		$_venue_id = 0;
		$_venue_id = (@is_numeric ($_REQUEST['venue_id'])) ? $_REQUEST['venue_id']:$conn->lastInsertId();
		
		if (DEBUG_MODE) { debug_message('Venue ID: '. $_venue_id); }

		// Valid Venue ID has been provided?
		if ( $_venue_id != 0 )
		{
		
			$_venue_location_lat = clean_string($_REQUEST['venue_location_lat']); // should keep 0.00 etc.
			$_venue_location_lon = clean_string($_REQUEST['venue_location_lon']); // should keep 0.00 etc.	

			
			if (DEBUG_MODE) { debug_message('Requested updated to Latitude: '. $_venue_location_lat .' Requested updated to Longitude: '. $_venue_location_lon ); }			

			
			// lat and log co-ords OK?
			// http://stackoverflow.com/questions/5756232/moving-lat-lon-text-columns-into-a-point-type-column
			if ( is_numeric($_venue_location_lat) && is_numeric($_venue_location_lon)   )
			{
				if (DEBUG_MODE) { echo 'Latitude and Longitude were OK.'; }
				
				$sql = 'UPDATE '. VENUE_TABLE .'
						SET 
						`venue_location_lat` = '. $_venue_location_lat .',
						`venue_location_lon` = '. $_venue_location_lon .',
						`venue_location_spatial_point` = GeomFromText(CONCAT(\'POINT (\', '. $_REQUEST['venue_location_lat']  .', \' \', '. $_REQUEST['venue_location_lon'] .', \')\'))
						WHERE venue_id = '. $_venue_id;
						
				if (DEBUG_MODE)
				{
					debug_message($sql);
				}
				
				
				// Do the spatial and geocoding update
				try
				{				
					$query = $conn->exec($sql);
				}
				catch(PDOException $e)
				{
					$failure_msg = "Error. Failed to execute database query: " . $e->getMessage();

				}
				
			}
			else
			{
				
				if (DEBUG_MODE) { echo 'Latitude and Longitude were not acceptable.'; }
				
				$failure_msg = 'Co-ordinates provided were either empty or invalid. Geocoding information not updated for venue, but everything else was.';
			}

		} // end venue_id check

	} // query sucess?
	
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
	
	<!-- Bootstrapper -->
    <link type="text/css" href="static/bootstrap-datepicker/css/datepicker.css" rel="stylesheet">

	<!-- Dyntable CSS -->
	<link type="text/css" href="static/jspkg-dynatable/jquery.dynatable.css" rel="stylesheet" />

	<!-- Leaflet CSS -->
	<link rel="stylesheet" href="static/leaflet/leaflet.css" />
	<script src="static/leaflet/leaflet.js"></script>
	
	
	<!-- Dropzone CSS -->
	<script type="text/javascript" src="static/dropzone-4.0.1/dist/dropzone.css"></script>	
	
	<style>
	#dropzone { margin-bottom: 3rem; }

	.dropzone { border: 2px dashed #0087F7; border-radius: 5px; background: white; }
	.dropzone .dz-message { font-weight: 400; }
	.dropzone .dz-message .note { font-size: 0.8em; font-weight: 200; display: block; margin-top: 1.4rem; }
	</style>
	
	
	<!-- JavaScript - Jquery -->
	<script type="text/javascript" src="static/jquery-2.1.4.min.js"> </script>
	
	<!-- JavaScript - Jquery UI -->	
	<script type="text/javascript" src="static/jquery-ui-1.11.4/jquery-ui.js"> </script>
	
	<!-- JavaScript - Dynatable -->		
	<script type="text/javascript" src="static/jspkg-dynatable/jquery.dynatable.js"> </script>
	
	<!-- Bootstrap -->
	<script type="text/javascript" src="static/bootstrap-3.3.4-dist/js/bootstrap.min.js"> </script>	
	
	<!-- Bootstrapper Datepicker -->
	<script type="text/javascript" src="static/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>    
	
	<!-- Dropzone -->
	<script type="text/javascript" src="static/dropzone-4.0.1/dist/dropzone.js"></script>



	<!-- JQUery Content Open -->
	<script type="text/javascript">
	$(function(){
	
	  // Tabbed Interface
	  $( "#tabs" ).tabs(); 

	  //Dynatable
	  $('#my-venue-table').dynatable();
	  
	  // Copy Date Button
	  $( "#copy_venue_hours-button" )
		  .button()
		  .click(function( event ) {
			  
			var standard_open 	= $('#venue_hour_open').val();
			var standard_close 	= $('#venue_hour_close').val();		
			
<?php

		// HACK ALERT: Using PHP to create JS!
		foreach ($days_in_a_week AS $day)
		{
		
			// this just happens to the database column name as well.
			$_form_variable_name_open 	= 'venue_hour_'. strtolower($day) .'_open'; // eg. 'venue_hour_monday_open'
			$_form_variable_name_close 	= 'venue_hour_'. strtolower($day) .'_close';
			
			// Javascript can do this for us
			// example: $('#txt_name').val('bla');
			
			echo '$(\'#'. $_form_variable_name_open .'\').val(standard_open)'."\r\n"; 
			echo '$(\'#'. $_form_variable_name_close .'\').val(standard_close)'."\r\n"; 	
		}
		
?>

		  });	
		  
		  
	  
	  // Date picker
		$('#venue_date_open-picker').datepicker({
			format: 'yyyy-mm-dd',
			todayBtn: 'linked'
		});
		
		$('#venue_date_close-picker').datepicker({
			format: 'yyyy-mm-dd',
			todayBtn: 'linked'
		});
		
		  
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
  <body>  
  
 
 


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
            <a class="navbar-brand" href="#">DateSpot&trade;</a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li><a href="?action=">Venue List</a></li>
              <li><a href="googlemap.php" target="scrotmap">Venue Map</a></li>
              <li><a href="?action=users">User List</a></li>
			  <li><a href="?action=log">Logging</a></li>	
			  <li><a href="?action=jsontest">JSON Query Test</a></li>			  
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
              <li><a href="" data-toggle="modal" data-target="#myModal">Version 0.00002</a></li>
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
        <div class="modal-body" style="background-image: url('static/scrot.jpg'); background-repeat:no-repeat; ">
          <p>Lots of bugs and things needs to be fixed with this. Version 0.00002 just fixed a few.</p>
		  <ul><li>Geo-Cordinates</li><li>Venue Image Upload</li><li>JSON to actually work</li><li>Events?</li><li>... and heaps of others to list here</li></ul>
		  
		  There's enough to allow us to start filling this place up with venues.
		  <br /><br />

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
		echo '<div class="alert alert-danger" role="alert"><strong>Error!</strong> '. $failure_msg.' .</div>';		
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
		  <th class="dyntable-head">Photos</th>
		  <th class="dyntable-head">Postcode</th>
		  <th class="dyntable-head">Address</th>
		  <th class="dyntable-head">Price</th>
		  <th class="dyntable-head">Quirky</th>
		  <th class="dyntable-head">Overall</th>		  
		 !<!-- <th class="dyntable-head">&nbsp;</th>		 -->	  

		</tr>
	  </thead>
	  <tbody>
<?php

	foreach (DateSpot::get_venue() AS $venue)
	{
		
		$sql = 'SELECT COUNT(*) as venue_image_count FROM '. VENUE_IMAGE_TABLE .' WHERE venue_id = '. $venue['venue_id'];
		
		$query = $conn->query($sql);
		$ic = $query->fetch();	
		
?>		
		
		<tr>
		  <td><?php echo $venue['venue_id']; ?></td>
		  <td><a href="?action=edit_venue&venue_id=<?php echo $venue['venue_id']; ?>"><?php echo $venue['venue_name']; ?></a></td>
		 <td><?php echo clip_string($venue['venue_description']); ?></td>
		  <td><a href="?action=edit_venue_image&venue_id=<?php echo $venue['venue_id']; ?>"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-camera" aria-hidden="true"></span> <?php echo $ic['venue_image_count']; ?></button></a>
</td>		 
		  <td><?php echo $venue['venue_postcode']; ?></td>
		  <td><?php echo $venue['venue_address']; ?></td>
		  <td><?php echo $venue['venue_rating_cost']; ?></td>
		  <td><?php echo $venue['venue_rating_quirkiness']; ?></td>
		  <td><?php echo $venue['venue_rating_general']; ?></td>
		<!---  <td valign="middle""><a href="?action=edit_venue&venue_id=<?php echo $venue['venue_id']; ?>"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button></a><a href="?action=edit_venue&venue_id=<?php echo $venue['venue_id']; ?>"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-camera" aria-hidden="true"></span></button></a> -->
		  
		  </td>		  
		  
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


	<script type="text/javascript">
	
	/* Make these variables global */
	var map, markerLayer, venue_form_lat, venue_form_lon;
	

	$(function(){
		
	  $( "#map" ).hide();	 /// by default hide the map
	  
	  /* Get the variables from the form */
	  venue_form_lat 	= $('#venue_location_lat').val();
	  venue_form_lon 	= $('#venue_location_lon').val();	
	

	  // Copy Date Button
	  $( "#show_map-button" )
		  .button()
		  .click(function( event ) {
							
			  // Use the JQuery isNumeric function
			  if ( $.isNumeric(venue_form_lat) && $.isNumeric(venue_form_lon) )
			  {	
				$( "#map" ).show();		// show the map
				$( "#show_map-button" ).hide(); // hide the button
				
				 /* OK so we're happy so show the map */
				 initializeMap();
			  }				

		  });	
	});
	
	
	function showClickSpot(e)
	{
			// Find the clicked position
			pos = e.latlng;
	 
			// Add the point where we clicked on the map
			new L.marker([e.latlng.lat, e.latlng.lng])
				.addTo(markerLayer)
				.bindPopup('Latitude: '+e.latlng.lat+'<br />Longitude: '+e.latlng.lng);
 
	}
	

	function initializeMap() {
		// Create a map object (roughly positioned over Eckstein Baby)
		map = L.map('map').setView([venue_form_lat,venue_form_lon], 16);

		// Select the tiles
		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		    minZoom: 10,
		    maxZoom: 20
		}).addTo(map);

		// Bind our click function to the map
		//map.on('click', findPoint);
		
		// If we already have a marker layer, remove it
		if(!!markerLayer)
			map.removeLayer(markerLayer);
		
		//alert(venue_form_lat);

		// Create a new marker layer
		markerLayer = new L.LayerGroup();
	//	for(i in data.points) {
			// Add markers for each of the points returned
			new L.marker([venue_form_lat, venue_form_lon])
				.addTo(markerLayer)
				.bindPopup('<?php echo $data['venue_name']; ?>');
	//	}

		// Add the point where we clicked on the map

		// new L.marker([e.latlng.lat, e.latlng.lng])
			// .addTo(markerLayer)
			// .bindPopup('Latitude: '+e.latlng.lat+'<br />Longitude: '+e.latlng.lng);

		// Add the layer to the map
		markerLayer.addTo(map);
	
		map.on('click', showClickSpot);
	}
	</script>
	
	
   <div class="form-group">
      <label class="control-label col-sm-2" for="venue_url">Geocoding: (<a href="https://support.google.com/maps/answer/18539?hl=en" target="_blank">From Google!</a>)</label>
      <div class="col-sm-3">
        <input type="text" class="form-control" name="venue_location_lat" id="venue_location_lat" value="<?php echo @$data['venue_location_lat']; ?>">Latitude (North/South) (i.e. 51.462416).
      </div>
      <div class="col-sm-3">
        <input type="text" class="form-control" name="venue_location_lon" id="venue_location_lon" value="<?php echo @$data['venue_location_lon']; ?>">Longitude (East/West) (i.e. -0.168803).
      </div>	  

        
	<div class="col-sm-2">

	  <a  class="btn btn-info btn" id="show_map-button">
	    <span class="glyphicon glyphicon-globe"></span> Show on Map
	  </a>

    	</div>

	</div>		
	
	
	<!-- Show the map as to where we're saying it is -->
	<div class="col-sm-12">

	<style type="text/css">
	#map {
		height: 300px;
		width: 100%;
		margin-bottom: 25px;
	}
	</style>	

	<!-- Show the mini Map -->
	<div id="map"><br /></div>
	</div>

	
	
	<!-- Datespot Operating Hours Attributes -->
	<h4 style="border-bottom: thin dotted #ccc; padding:10px;">Venue Operating Hours / Days</h4>

	<!--
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
			
			echo '<label class="btn btn-default'. $_active .'"><input name="venue_open_monday" value="Y" type="checkbox" autocomplete="off"'. $_checked .">Venue Open Mondays</label>\r\n\r\n";
		
?>
		
		</div>
		Tuesday to Saturday are assumed as standard operating days.
      </div>
    </div>	
	
	-->
	
	

	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Standard Hours:</label>
      
	  <div class="col-sm-2"><input type="text" class="form-control" name="venue_hour_open" id="venue_hour_open" value="<?php echo @$data['venue_hour_open']; ?>"> Opening (HH:MM:SS)</div> 
	  <div class="col-sm-2"><input type="text" class="form-control" name="venue_hour_close" id="venue_hour_close" value="<?php echo @$data['venue_hour_close']; ?>"> Closing (HH:MM:SS)</div>
	  <div class="col-sm-2"><button type="button" class="btn btn-default" id="copy_venue_hours-button">Copy to Monday-Sunday</button></div>	  
    </div>		
	
	
<?php

	// Get the individual operating hours for each day.
	foreach ($days_in_a_week AS $day)
	{
		
		// this just happens to the database column name as well.
		$_form_variable_name_open 	= 'venue_hour_'. strtolower($day) .'_open'; // eg. 'venue_hour_monday_open'
		$_form_variable_name_close 	= 'venue_hour_'. strtolower($day) .'_close';
		
		// It's a bit of a hack using echo @$data[$_form_variable_name_open]; as an inner variable to get the array value from $data, but it'll work.
		
?>
		<div class="form-group">
		  <label class="control-label col-sm-2" for="venue_address"><?php echo $day; ?> Hours:</label>
		  
		  <div class="col-sm-2"><input type="text" class="form-control" name="<?php echo $_form_variable_name_open; ?>"  id="<?php echo $_form_variable_name_open; ?>"  value="<?php echo @$data[$_form_variable_name_open]; ?>"></div> 
		  <div class="col-sm-2"><input type="text" class="form-control" name="<?php echo $_form_variable_name_close; ?>" id="<?php echo $_form_variable_name_close; ?>" value="<?php echo @$data[$_form_variable_name_close]; ?>"></div>

<?php 	
		 // Only show this if it's not a new venue
		 if ( !empty ($data) )
		 {
		 
			 if ( @$data[$_form_variable_name_open] == @$data[$_form_variable_name_close])
			 {
				 echo '<div class="col-sm-3" ><div style="padding: 5px; color: #000; background-color: #fcf8e3; border-color: #faebcc;">Venue Closed (Open Time = Close Time)</div></div>';
			 }
		}
?>
		</div>		
	
	
<?php

	}
	
?>


	
	<!-- Datespot Operating Hours Attributes -->
	<h4 style="border-bottom: thin dotted #ccc; padding:10px;">Venue Operating Life & Frequency</h4>
	<h5>This is only of interest to venues which only have a specific life. ie. Pop-up stalls etc. If the venue is an 'ongoing concern' (&#10084; the accounting terminology) then leave blank.</h5>
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Operating Life:</label>
      
	  <div class="col-sm-2">
		<div class="input-append date" id="dp1" data-date="<?php echo @$data['venue_date_open']; ?>" data-date-format="yyyy-mm-dd"><input type="text" class="form-control" name="venue_date_open" id="venue_date_open-picker" value="<?php echo @$data['venue_date_open']; ?>"> Opens (YYYY-MM-DD)</div>
	  </div>

	  <div class="col-sm-2">
		<div class="input-append date" id="dp1" data-date="<?php echo @$data['venue_date_close']; ?>" data-date-format="yyyy-mm-dd"><input type="text" class="form-control" name="venue_date_close" id="venue_date_close-picker" value="<?php echo @$data['venue_date_close']; ?>"> Gone (YYYY-MM-DD)</div>
	  </div>
	</div>	

	
    <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Frequency:</label>
      
      <div class="col-sm-8">
        <input type="text" class="form-control" name="venue_date_operating_frequency_1" id="venue_date_operating_frequency_1" value="<?php echo @$data['venue_date_operating_frequency_1']; ?>">Eg: 'last thursday of next month'. Must be according to PHP's <a href="http://php.net/dateperiod" target="_blank">DatePeriod</a> and <a href="http://php.net/manual/en/datetime.formats.relative.php" target="_blank">Relative</a> date interval formats!
      </div>
	  
	</div>	
	<!--
	 <div class="form-group">
      <label class="control-label col-sm-2" for="venue_address">Frequency 2:</label>
     
      <div class="col-sm-10">
        <input type="text" class="form-control" name="venue_date_operating_frequency_2" id="venue_date_operating_frequency_2" value="<?php echo @$data['venue_date_operating_frequency_2']; ?>">
      </div>	
	</div>	
	-->
	
	

	
	<!-- Datespot Venue Attributes -->
	<h4 style="border-bottom: thin dotted #ccc; padding:10px;">DateSpot&trade; Attributes</h4>
	
	
	
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_rating_quirkiness">Quirkiness Rating:</label>
      <div class="col-sm-1">
        <input type="text" class="form-control" name="venue_rating_quirkiness" id="venue_rating_quirkiness" value="<?php echo @$data['venue_rating_quirkiness']; ?>">
      </div>
	  <div class="col-sm-4">
	  		A number from 1 to 10 of the 'Quirky/Weird' a venue is. 10 being ridiculous. Subjective.</div>
    </div>	
	
	
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_rating_cost">Price Rating:</label>
      <div class="col-sm-1">
        <input type="text" class="form-control" name="venue_rating_cost" id="venue_rating_cost" value="<?php echo @$data['venue_rating_cost']; ?>">
      </div>
	    <div class="col-sm-4">
	  		A number from 1 to 10 of the 'price' of a venue. 1 being inexpensive, 10 being break the bank. Subjective.
			</div>
    </div>	
	
	<div class="form-group">
      <label class="control-label col-sm-2" for="venue_rating_general">Overall Rating:</label>
      <div class="col-sm-1">
        <input type="text" class="form-control" name="venue_rating_general" id="venue_rating_general" value="<?php echo @$data['venue_rating_general']; ?>">
      </div>
	    <div class="col-sm-4">
	  		A number from 1 to 10 of the 'awesomeness' of a venue. 10 being fantastic. Subjective.
			</div>
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
	if ( $_REQUEST['action'] == 'edit_venue_image' ) 
	{

		$data = array();
		
		$actiontitle = 'Manage Venue Images';
		
		if (!isset($_REQUEST['venue_id']) || !is_numeric ($_REQUEST['venue_id']))
		{
			echo 'No Venue ID was provided';
			exit();
		}
		
	///	print_r($data);

?>

  <div class="contentbox">
  
  <h3 style="border-bottom: thin solid #ccc; padding:10px;"><?php echo $actiontitle; ?></h3>



  <div id="dropzone"><form action="http://www.torrentplease.com/dropzone.php" class="dropzone" id="demo-upload">

  <div class="dz-message">
    Drop files here or click to upload.<br />
    <span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
  </div>

</form></div>







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

