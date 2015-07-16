<?php

$testcase = array
(
	'last friday of next month',
	'first tuesday of next month',
	'second wednesday of next month',
	'second sunday of next month',
	'!&(@&(#&*@'
);


$begin = new DateTime( );
print_r($begin);

$end = new DateTime( );
$end->modify('+2 months');

print_r($end);

foreach ($testcase AS $test)
{
	echo '<br /><br /><b>Testing Test Case: '. $test .'</b><br />';
	$interval = DateInterval::createFromDateString($test);
	//$period = new DatePeriod($begin, $interval, $end, DatePeriod::EXCLUDE_START_DATE);
	$period = new DatePeriod($begin, $interval, 1, DatePeriod::EXCLUDE_START_DATE);
	

	foreach ( $period as $dt ){
	  echo $dt->format( "l Y-m-d H:i:s\n" );
	  echo ' or in MySQL format ';
	  echo $dt->format( "Y-m-d" );
	  echo '<br />';
	}
  
}
  