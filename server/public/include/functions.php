<?php
/**********************************************************************
 *
 *	script file		: functions.php
 *	
 *	begin			: 30 May 2015
 *	copyright		: Grant Bartlett and Ben Stein
 *  descriptions	: Common functions used throughout the Server
 *					  Side application
 *
 **********************************************************************/

 
//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $timezone)
{
	return @gmdate($format, $gmepoch + (3600 * $timezone));
}


//
// Clips a string and adds trailing dots
// 
function clip_string($string, $length = 255) 
{

	if ( (strlen($string)) > $length ) 
	{
	   $string = substr($string, 0, $length);
	   $string = substr_replace ($string,'...', $length-3);
	}
	
	//$string = ereg_replace("[^[:space:]a-zA-Z0-9*_.-]", "", strip_tags($string)); // remove it if it ain't alphanumeric
	
	// Remove dangerous HTML and other artifacts (incase a string is clipped before a tag has been closed etc....).
    $string = strip_tags($string);            

	return $string;
	
}


// Clean potentially nasty stuff from a string
function clean_string($string)
{
	
	
   // Return only spaces, a-z, A-Z, 0-9, *, _, ., -, ','
   return preg_replace('/[^a-zA-Z0-9\.\,\/:\- ]/', '', $string);
   
   
/* 	
	Because i search a lot 4 this:

	The following should be escaped if you are trying to match that character

	\ ^ . $ | ( ) [ ]
	* + ? { } ,

	Special Character Definitions
	\ Quote the next metacharacter
	^ Match the beginning of the line
	. Match any character (except newline)
	$ Match the end of the line (or before newline at the end)
	| Alternation
	() Grouping
	[] Character class
	* Match 0 or more times
	+ Match 1 or more times
	? Match 1 or 0 times
	{n} Match exactly n times
	{n,} Match at least n times
	{n,m} Match at least n but not more than m times
	More Special Character Stuff
	\t tab (HT, TAB)
	\n newline (LF, NL)
	\r return (CR)
	\f form feed (FF)
	\a alarm (bell) (BEL)
	\e escape (think troff) (ESC)
	\033 octal char (think of a PDP-11)
	\x1B hex char
	\c[ control char
	\l lowercase next char (think vi)
	\u uppercase next char (think vi)
	\L lowercase till \E (think vi)
	\U uppercase till \E (think vi)
	\E end case modification (think vi)
	\Q quote (disable) pattern metacharacters till \E
	Even More Special Characters
	\w Match a "word" character (alphanumeric plus "_")
	\W Match a non-word character
	\s Match a whitespace character
	\S Match a non-whitespace character
	\d Match a digit character
	\D Match a non-digit character
	\b Match a word boundary
	\B Match a non-(word boundary)
	\A Match only at beginning of string
	\Z Match only at end of string, or before newline at the end
	\z Match only at end of string
	\G Match only where previous m//g left off (works only with /g)   
 */   
}


function utf8_encode_all($dat) // -- It returns $dat encoded to UTF8 
{ 
  if (is_string($dat)) return utf8_encode($dat); 
  if (!is_array($dat)) return $dat; 
  $ret = array(); 
  foreach($dat as $i=>$d) $ret[$i] = utf8_encode_all($d); 
  return $ret; 
} 

/* ....... */ 

function utf8_decode_all($dat) // -- It returns $dat decoded from UTF8 
{ 
  if (is_string($dat)) return utf8_decode($dat); 
  if (!is_array($dat)) return $dat; 
  $ret = array(); 
  foreach($dat as $i=>$d) $ret[$i] = utf8_decode_all($d); 
  return $ret; 
} 


function debug_message($message)
{
	echo $message."\r\n <br />";
}

