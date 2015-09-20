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
include('../include/class_thumbnailer.php'); 	// for image resizing


/*

	var $image_aspects	= array 
	(
		'square' 		=> array ( 'width' => 480, 'height' => 480),	// smaller square size
		'fourbythree'	=> array ( 'width' => 640, 'height' => 480), 	// Usual 4:3 format
		'iphone6'		=> array ( 'width' => 1334,'height' => 750),	// iPhone 6, 16:9 screen aspect ratio
		'original'		=> array ( 'width' => 0,'height' => 0)			// hack for the resizer to keep original	
	);
	
	
*/


	echo '<html><head><title>Mass Image Resizer</title></head><body>';
	
	if ( !is_numeric($_GET['offset'])) { $offset = 0; } else { $offset = $_GET['offset']; }
	$limit = 20;

	$sql = 'SELECT venue_image_id, venue_image_data_original 
			FROM '. VENUE_IMAGE_TABLE .' 
			ORDER BY venue_image_id ASC
			LIMIT '. $offset .', '. $limit;
			
	echo $sql .'<br />';
			
	 // Do the query
	$query = $conn->query($sql);
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) 
	{ 
		echo 'Loading resized images for venue_image_id: '. $row['venue_image_id'] .'<br />';
	
		// Insert into the database
		$_sql = 'UPDATE '. VENUE_IMAGE_TABLE .'
				SET
				`venue_image_data_resized_square` 		= \''. addslashes(generate_jpeg_image_binary($row['venue_image_data_original'],480,480)) .'\', 
				`venue_image_data_resized_fourbythree`	= \''. addslashes(generate_jpeg_image_binary($row['venue_image_data_original'],640,480)) .'\',
				`venue_image_data_resized_iphone6` 		= \''. addslashes(generate_jpeg_image_binary($row['venue_image_data_original'],1334,750)) .'\'
				WHERE venue_image_id = '. $row['venue_image_id'];
				
		// Try to insert the new image stuff
		try
		{				
			$_query = $conn->exec($_sql);
		}
		catch(PDOException $e)
		{
			$failure_msg = "Error. Failed to execute database query: " . $e->getMessage();
		}
		
		echo '.... Completed! <br />';		
		
		unset($_query);
		unset($_sql);

	} // unset
	
	echo '<br /><a href="?offset='. ($offset+$limit).'">Convert next 20</a>';

		
		

	function generate_jpeg_image_binary($binary, $width, $height)
	{

			$imagick = new \Imagick();
			$imagick->readImageBlob($binary);
			$imagick->cropThumbnailImage($width, $height);
			$imagick->setImageCompressionQuality(60);	
			$imagick->setImageFormat('jpeg');
			
			return $imagick->getImageBlob();
	}
	
	echo '</body></html>';
