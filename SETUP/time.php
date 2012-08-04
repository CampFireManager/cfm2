<?php

/*
 *      time.php
 *      
 *      Copyright 2012 michael 'heeed' rimicans <heed@bigmassiveheed.co.uk>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
 

echo "A concept demo for the auto calculation of session start time\n\n";

date_default_timezone_set('UTC');



$open_time = '10:00:00';
$close_time = '17:00';
$sessions = '8';
$date='2012/08/19 ';

$time_avail = strtotime($close_time)-strtotime($open_time); /*seconds in an hour*/
$time_hours = $close_time-$open_time;
$event_date = $date.' '.$open_time;

$session_time = $time_avail/$sessions;
$session_time = gmdate("i:s", $session_time);
$session_time = explode(':',$session_time);
//print_r($session_time);
//$session_time = round($session_time, PHP_ROUND_HALF_UP);


echo "Opening time:\n ".$open_time."\n";
echo "Closing time:\n ".$close_time."\n";
//echo "Time availible (in hours):\n ".$time_hours."\n Time availible (in seconds):".$time_avail."\n";

echo $sessions." Sessions required. \n\nThis will give you session times of:\n".$session_time[0]."m".$session_time[1]."s";

echo "\nSession Start Timings\n";

$initDate2 = new DateTime($event_date);
$DateTimeZone = timezone_open('UTC');
$initDate2 -> setTimezone($DateTimeZone);

echo $initDate2->format("G:i:s")." Start \n";

$counter = 1;
do
{
	
	$initDate2->add(new DateInterval("PT".$session_time[0]."M".$session_time[1]."S"));
	echo $initDate2->format("G:i:s \n");
	$counter ++;
}
while($counter < $sessions);
$initDate2->add(new DateInterval("PT".$session_time[0]."M".$session_time[1]."S"));
echo $initDate2->format("G:i:s")." Finished \n";
?>
