<?php
/**********************************************************************
 *
 *	script file		: thumbnailer.php
 *	
 *	begin			: 
 *	copyright		: 
 *  descriptions	: For the creation of image thumbnails and uploading
 *					  to the database
 *
 **********************************************************************/
 
 class ThumbNailer
 {
	
	// The temporary file path
	var $temporary_image_file = '';
	var $temporary_image_type = '';
	
	var $image_aspects	= array 
	(
		'square' 		=> array ( 'width' => 480, 'height' => 480),	// smaller square size
		'fourbythree'	=> array ( 'width' => 640, 'height' => 480), 	// Usual 4:3 format
		'iphone6'		=> array ( 'width' => 1334,'height' => 750),	// iPhone 6, 16:9 screen aspect ratio
		'original'		=> array ( 'width' => 0,'height' => 0)			// hack for the resizer to keep original	
	);
	
	
	// We store these as JPEG
	var $output_quality = 55;
	var $output_format  = 'image/jpeg'; 	// or image/png  // or image/gif
	var $output_binary  = ''; 			// the binary of the image

	// Hash
	var $image_md5_original_hash = '';

	// Error message should one be required
	var $error 	=  ''; 
	
	// Load an image that has been uploaded to /tmp via PHP upload fun
	function check_image($name, $temporary_file)
	{
	
		/*
		$name= $_FILES["myfile"]["name"]; //of interest
		$type= $_FILES["myfile"]["type"];
		$temp= $_FILES["myfile"]["tmp_name"]; // of interest
		$size= $_FILES["myfile"]["size"];
		$error= $_FILES["myfile"]["error"]; // not handled in this class
		*/
		
		if (empty($name) || empty($temporary_file) )
		{
			$this->error = 'Image name or location of temporary file was not provided.';
			return false;
		}
		
	
		// Get Image Size
		$check = getimagesize($temporary_file);
	
		if($check !== false) 
		{
				$this->error 	= 'File is an image - ' . $check['mime'] . '.';
				//debug_message('File is an image - ' . $check['mime']);
		} 
		else 
		{
				$this->error 	= 'File is not an image.';
				return false;
		}
	
		$imageFileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));


		// Allow certain file formats
		if ($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif' ) 
		{
			$this->error = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
			return false;
		}
		
		
		// So all is good, then store this in the class as a valid temporary file
		$this->temporary_image_file = $temporary_file;
		$this->temporary_image_type = $imageFileType;
		
		// Generate MD5 hash
		$this->image_md5_original_hash = md5_file($temporary_file);
		

		return true;
	
	} // end check_uploaded_image
	
	
	// Process the images
	function process_image($name, $temporary_file, $aspect)
	{
		/*
		$name= $_FILES["myfile"]["name"]; //of interest
		$type= $_FILES["myfile"]["type"];
		$temp= $_FILES["myfile"]["tmp_name"]; // of interest
te		$size= $_FILES["myfile"]["size"];
		$error= $_FILES["myfile"]["error"]; // not handled in this class
		*/
		
		
		if ( !($this->check_image($name, $temporary_file) ) )
		{
			// whoever is using $class->process_image can check for calso and then see what's in $class->error
			return false;	
			
		}
		
		// Images aspect sizes.
		if ( !array_key_exists($aspect, $this->image_aspects) )
		{
			$this->error = 'The requested aspect ratio is not a possible option';
			debug_message($this->error);
			return false;
		
		}
		
		// Requested pixel aspect ratio
		$width 	= $this->image_aspects[$aspect]['width'];
		$height = $this->image_aspects[$aspect]['height'];

		// Do the last step
		return $this->_resize_and_crop_imagemagik($width, $height);
		
	} // process_image
	

	// Image Magic Format
	function _resize_and_crop_imagemagik($width, $height)
	{
	
		//debug_message('The requested resize width is: 	'. $width);
		//debug_message('The requested resize height is: 	'. $height);	

		// Are we jumping the gun here?
		if ( empty($this->temporary_image_file) || empty($this->temporary_image_type) )
		{
			$this->error 	= 'Cannot perform resize as no image has been checked or loaded yet.';
			return false;		
		}
		
		
		
		// What size of image are we resizing too?
		/*
		if ($thumbnail) { $thumb_w = $this->thumbnail_width; $thumb_h = $this->thumbnail_height; }
		else			{ $thumb_w = $this->fullsize_width;  $thumb_h = $this->fullsize_height; }
		*/
		
	
	    $imagick = new \Imagick(realpath($this->temporary_image_file));
//		$imagick->scaleImage($thumb_w, $thumb_h);

		// Don't attempt to be smart and resize / if it's too small, or pointless
		if ( ($width > 4) && ($height > 4) )
		{
			    $imagick->cropThumbnailImage($width, $height);
				$imagick->setImageCompressionQuality($this->output_quality);		// HACK		
		}
		else
		{
			$imagick->setImageCompressionQuality(90);		// Keep a high quality original		
		}		
		
		// Output format is according to top
		switch ($this->output_format)
		{
			case 'image/gif': $imagick->setImageFormat('gif'); break;
			case 'image/png': $imagick->setImageFormat('png'); break;
			default: $imagick->setImageFormat('jpeg'); break;
		}
		
		// Set the binary into the classes stores
		$this->output_binary = $imagick->getImageBlob();
		
		// All is good so return true
		return true;
		
	} // end ik resize and crop
	
	
	
	
	
	/***
	 * Note: Old/No longer used
	 *
	 * Perform the image load and resize based on pre-configured sizes within the class
	 * declaration. Attempt to perform the resize.
	 * 
	 * Returns false on error OR an array with the content type and data on success
	 *
	 */
	 // FIXME: Does not scale an image up.
	function _resize_image_depreciated($thumbnail = true, $crop = 1)
	{
		// Are we jumping the gun here?
		if ( empty($this->temporary_image_file) || empty($this->temporary_image_type) )
		{
			$this->error 	= 'Cannot perform resize as no image has been checked or loaded yet.';
			return false;		
		}

		// Is it a valid image?
		if(!list($w, $h) = getimagesize($this->temporary_image_file))
		{
			$this->error 	= 'Cannot resize. Unsupported picture type!';
			return false;
		}

		//$type = strtolower(substr(strrchr($this->temporary_image_file,"."), 1));
		$type = $this->temporary_image_type;
		if($type == 'jpeg') $type = 'jpg';
		switch($type)
		{
			case 'bmp': $img = imagecreatefromwbmp($this->temporary_image_file); break;
			case 'gif': $img = imagecreatefromgif($this->temporary_image_file); break;
			case 'jpg': $img = imagecreatefromjpeg($this->temporary_image_file); break;
			case 'png': $img = imagecreatefrompng($this->temporary_image_file); break;
			
			default : 
				$this->error 	= 'Unsupported picture type!';
				return false;
		}
	
		// echo 'so we have an image?';	
		
		// What size of image are we resizing too?
		if ($thumbnail) { $width = $this->thumbnail_width; $height = $this->thumbnail_height; }
		else			{ $width = $this->fullsize_width;  $height = $this->fullsize_height; }

	


		/* TODO: Need to make this scale up and scale down.
			http://stackoverflow.com/questions/14096065/php-resize-image-proportionally-to-a-bigger-size
			http://php.net/manual/en/function.imagecopyresampled.php
		*/

		// Perform the resize
		if($crop)
		{
			// crop and fit to full new image
			if($w < $width or $h < $height) { $this->error = 'Picture is too small!'; return false; }
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;
		}
		else 
		{
			// fit the picture, might have empty space
			if($w < $width and $h < $height) { $this->error = 'Picture is too small!'; return false; }
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
			$x = 0;
		}

		$new = imagecreatetruecolor($width, $height);

		// preserve transparency
		if ($type == 'gif' or $type == 'png')
		{
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
		}

		imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);


		/* From: http://php.net/manual/en/function.imagejpeg.php
		*
		* imageXXX() only has two options, save as a file, or send to the browser.
		* It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
		* So I start the output buffering, use imageXXX() to output the data stream to the browser, 
		* get the contents of the stream, and use clean to silently discard the buffered contents.
		*/
		
		ob_start();

		// Output format is according to top
		switch ($this->output_format)
		{
			case 'image/gif': imagegif($new); break;
			case 'image/png': imagepng($new, NULL, 0); break; // no compression
			default: imagejpeg($new, NULL, 75);  break;
		}

		$final_image = ob_get_contents();
		ob_end_clean();
		
		// Set the binary into the classes stores
		$this->output_binary = $final_image;
		
		// All is good so return true
		return true;
		
	} // end resize_image
	
	
	
	// RESIZE AN IMAGE PROPORTIONALLY AND CROP TO THE CENTER
	// Code from: http://stackoverflow.com/questions/14096065/php-resize-image-proportionally-to-a-bigger-size
	function _resize_and_crop_gd($thumbnail = true)
	{
		$quality = 75;
		
		
		// Are we jumping the gun here?
		if ( empty($this->temporary_image_file) || empty($this->temporary_image_type) )
		{
			$this->error 	= 'Cannot perform resize as no image has been checked or loaded yet.';
			return false;		
		}

		// Is it a valid image?
		if(!list($w, $h) = getimagesize($this->temporary_image_file))
		{
			$this->error 	= 'Cannot resize. Unsupported picture type!';
			return false;
		}

		$type = $this->temporary_image_type;
		if($type == 'jpeg') $type = 'jpg';
		switch($type)
		{
			case 'bmp': $original = imagecreatefromwbmp($this->temporary_image_file); break;
			case 'gif': $original = imagecreatefromgif($this->temporary_image_file); break;
			case 'jpg': $original = imagecreatefromjpeg($this->temporary_image_file); break;
			case 'png': $original = imagecreatefrompng($this->temporary_image_file); break;
			
			default : 
				$this->error 	= 'Unsupported picture type!';
				return false;
		}
		
		if ( !$original )
		{
				$this->error 	= 'Failed to load original image.';
				return false;			
		}
		
		
		// What size of image are we resizing too?
		if ($thumbnail) { $thumb_w = $this->thumbnail_width; $thumb_h = $this->thumbnail_height; }
		else			{ $thumb_w = $this->fullsize_width;  $thumb_h = $this->fullsize_height; }

		
		// GET ORIGINAL IMAGE DIMENSIONS
		list($original_w, $original_h) = getimagesize($this->temporary_image_file);

		// RESIZE IMAGE AND PRESERVE PROPORTIONS
		$thumb_w_resize = $thumb_w;
		$thumb_h_resize = $thumb_h;
		if ($original_w > $original_h)
		{
			$thumb_h_ratio  = $thumb_h / $original_h;
			$thumb_w_resize = (int)round($original_w * $thumb_h_ratio);
		}
		else
		{
			$thumb_w_ratio  = $thumb_w / $original_w;
			$thumb_h_resize = (int)round($original_h * $thumb_w_ratio);
		}
		
		if ($thumb_w_resize < $thumb_w)
		{
			$thumb_h_ratio  = $thumb_w / $thumb_w_resize;
			$thumb_h_resize = (int)round($thumb_h * $thumb_h_ratio);
			$thumb_w_resize = $thumb_w;
		}

		// CREATE THE PROPORTIONAL IMAGE RESOURCE
		$thumb = imagecreatetruecolor($thumb_w_resize, $thumb_h_resize);
		if (!imagecopyresampled($thumb, $original, 0,0,0,0, $thumb_w_resize, $thumb_h_resize, $original_w, $original_h))
		{
			$this->error = 'Could not perform image copy.';
			return false;
		}

		// ACTIVATE THIS TO STORE THE INTERMEDIATE IMAGE
		// imagejpeg($thumb, 'RAY_temp_' . $thumb_w_resize . 'x' . $thumb_h_resize . '.jpg', 100);

		// CREATE THE CENTERED CROPPED IMAGE TO THE SPECIFIED DIMENSIONS
		$final = imagecreatetruecolor($thumb_w, $thumb_h);

		$thumb_w_offset = 0;
		$thumb_h_offset = 0;
		if ($thumb_w < $thumb_w_resize)
		{
			$thumb_w_offset = (int)round(($thumb_w_resize - $thumb_w) / 2);
		}
		else
		{
			$thumb_h_offset = (int)round(($thumb_h_resize - $thumb_h) / 2);
		}

		if (!imagecopy($final, $thumb, 0,0, $thumb_w_offset, $thumb_h_offset, $thumb_w_resize, $thumb_h_resize)) return FALSE;

		
	   /* From: http://php.net/manual/en/function.imagejpeg.php
		*
		* imageXXX() only has two options, save as a file, or send to the browser.
		* It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
		* So I start the output buffering, use imageXXX() to output the data stream to the browser, 
		* get the contents of the stream, and use clean to silently discard the buffered contents.
		*/
		
		ob_start();


		// Output format is according to top
		switch ($this->output_format)
		{
			case 'image/gif': imagegif($final); break;
			case 'image/png': imagepng($new, NULL, 0); break; // no compression
			default: imagejpeg($final, NULL, 75);  break; // best wuality vs size
		}

		$final_image = ob_get_contents();
		ob_end_clean();

		// Set the binary into the classes stores
		$this->output_binary = $final_image;
		
		// All is good so return true
		return true;
		
	
	} // resize and crop

}

  



class ThumbNailerTest extends ThumbNailer
{

	function ThumbNailerTest()
	{
	
		switch ($_REQUEST['action'])
		{

				case 'process_image_upload':  // NOT ACTUALLY USED
						$this->process_image_upload();
						break;

				default:
						$this->show_image_upload_form(); // Get the Nearest Venues
						break;
		}

		// print_r($_REQUEST);

	
	}
	
	
	function show_image_upload_form()
	{
?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Image Upload Test</title>
		</head>
		<body>

		<form action="<?php $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
			Select image to upload:
			<input type="file" 		name="fileToUpload" id="fileToUpload" />
			<input type="submit" 	value="Upload Image" name="submit" />
			<input type="hidden"	value="process_image_upload" name="action" />
		<select name="resize_method"><option value="gd">Use GD Function</option><option value="magik">Use Imagemagik</option></select>
		</form>

		</body>
		</html>

<?php	
	
	} // end show_upload_form
	
	
	function process_image_upload()
	{
	
	/*
		// From PHP $_FILE
		$name 			= $FILE['name'];
		$temporary_file = $FILE['tmp_name'];

		// Check that the upload was actually valid
		if ( !isset($FILE) || ($FILE['error'] !== UPLOAD_ERR_OK) )
		{
			$this->error = 'An error has occurred with the image upload. Please try again.';
			return false;
		}

		*/
//		print_r($FILE);


	
		/*
	
		http://php.net/manual/en/features.file-upload.php
	
		$name= $_FILES["myfile"]["name"]; //of interest
		$type= $_FILES["myfile"]["type"];
		$size= $_FILES["myfile"]["size"];
		$temp= $_FILES["myfile"]["tmp_name"]; // of interest
		$error= $_FILES["myfile"]["error"]; // not handled in this class
		*/
		
	
		if ( !($this->_check_image($_FILES["fileToUpload"]['name'], $_FILES["fileToUpload"]['tmp_name'] ) ) )
		{
			echo 'There was an error with the file: '. $this->error;
			return;
		}


		if ($_REQUEST['resize_method'] == 'magik') 
		{	
			if ( !($data = $this->_resize_and_crop_imagemagik(true)) )
			{
				echo 'There was an error with the image resize: '. $this->error;
				return;		
			}
		}
		else
		{
					if ( !($data = $this->_resize_and_crop_gd(true)) )
					{
							echo 'There was an error with the image resize: '. $this->error;
							return;
					}
		}	
		
			
		header('Content-Type: '. $this->output_format);
		echo $this->output_binary;
			
		// echo 'Apparently it went OK!';


	} // end process_image_upload

} // end ThumbNailerTest


// new ThumbNailerTest();

/*

		// From PHP $_FILE
		$name 			= $FILE['name'];
		$temporary_file = $FILE['tmp_name'];

		// Check that the upload was actually valid
		if ( !isset($FILE) || ($FILE['error'] !== UPLOAD_ERR_OK) )
		{
			$this->error = 'An error has occurred with the image upload. Please try again.';
			return false;
		}

*/
