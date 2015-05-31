<?php
/**********************************************************************
 *
 *	script file		: class_datespot.php			
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *	description		: This is the core logic for the delivering of the 
 *					  venue information to the mobile client. 
 *
 **********************************************************************/


if ( !defined('IN_APPLICATION') )
{
	die('Error. This file is not directly accessed.');
}


//
// Begin DateSpot Logic to the underlying database
//
class DateSpot
{
	
	function DateSpot()
	{
		if (DEBUG_MODE)
		{
			debug_message('Date class has been initialised');
		}

	}
	
	static function get_venue($venue_id = null)
	{
		global $conn;
		
		if (!is_numeric($venue_id))
		{
			$sql = 'SELECT * FROM '. VENUE_TABLE .' ORDER BY venue_id ASC';
		}
		else
		{
			$sql = 'SELECT * FROM '. VENUE_TABLE .' WHERE venue_id = '. $venue_id;
		}
	
		/*
		$query = $conn->prepare($sql);
		$query->execute($id);
		*/
		
		$query = $conn->query($sql);
		$data = $query->fetchAll(PDO::FETCH_ASSOC);	

		return $data;
		
	}
	

}


