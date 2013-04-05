<?php

/**
 * Formats a MySQL date/datetime string into MM/DD/YYYY
 *
 * @author Jeremiah Poisson
 * @param string $date
 * @return string
 */
function F_Date($date='') {

    // Variables
    $year = substr($date, 0, 4);
    $month = substr($date, 5, 2);
    $day = substr($date, 8, 2);

    return $month . "/" . $day . "/" . $year;
}

/**
 * Takes a MySQL datetime string and returns just the time.
 *
 * @author Jeremiah Poisson
 * @param string $time
 * @return string
 */
function Format_Time($time='') { return substr($time, 0, 5); }

/**
 * Takes a MySQL datetime string and returns it formated as MM/DD/YYY HH:MM[AM,PM]
 *
 * @author Jeremiah Poisson
 * @param string $date
 * @return string
 */
function F_DateTime($date='') {
    $year = substr($date, 0, 4);
    $month = substr($date, 5, 2);
    $day = substr($date, 8, 2);
    $hour = substr($date,11,2);
    $min = substr($date,14,2);
    $sec = substr($date,17,2);

    return date("m-d-Y @ g:ia", mktime($hour,$min,$sec,$month,$day,$year));
}

/**
 * Takes a MySQL datetime string and returns either AM or PM.
 *
 * @author Jeremiah Poisson
 * @param string $date
 * @return string
 */
function F_getAMPM($date='') {
    $year = substr($date, 0, 4);
    $month = substr($date, 5, 2);
    $day = substr($date, 8, 2);
    $hour = substr($date,11,2);
    $min = substr($date,14,2);
    $sec = substr($date,17,2);

    return date("a", mktime($hour,$min,$sec,$month,$day,$year));
}

/**
 * Takes a date string with the following format MM/DD/YYYY HH:MM:SS and
 * formats it as a MySQL date string (YYY-MM-DD)
 *
 * @author Jeremiah Poisson
 * @param string $date
 * @return string
 */
function F_dateToMySQL($date) {
    $month = substr($date, 0, 2);
    $day = substr($date, 3, 2);
    $year = substr($date, 6, 4);
    $hour = substr($date,11,2);
    $min = substr($date,14,2);
    $sec = substr($date,17,2);

    return date("Y-m-d", mktime($hour,$min,$sec,$month,$day,$year));
}

/**
 * Turns an array of time into a string
 *
 * @param array $time The time array to turn into a string
 * @return string
 * @author Jeremiah Poisson
 **/
function timearrayToString($time) { return date("g:i a",mktime($time[0],$time[1])); }

/**
 * Takes two MySQL dates and finds out the
 * time difference in days between them
 *
 * @param  string $dformat
 * @param  string $endDate
 * @param  string $beginDate
 * @return integer
 */
function dateDiff($dformat, $endDate, $beginDate) {
    $date_parts1=explode($dformat, $beginDate);
    $date_parts2=explode($dformat, $endDate);
    $start_date=gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
    $end_date=gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
    return $end_date - $start_date;
}

/**
 * This function calculates the first [DAY] of a month.
 * The day to find is passed as an integer to the function.
 *
 * To use: Pass the month, year and day (as an integer 0-6) to the function.
 *
 * @param int $month
 * @param int $year
 * @param int $day  [0 = sunday, 1 = monday, 2 = tuesday, 3 = wednesday, 4 = thursday, 5 = friday, 6 = saturday]
 * @return date
 */
function getFirstDay($month,$year,$day){

    $num = date("w",mktime(0,0,0,$month,1,$year));
    if($num==$day) {
        return date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
    } else if($num>$day) {
        return date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year)+(86400*((7+$day)-$num)));
    } else {
        return date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year)+(86400*($day-$num)));
    }

}

/**
 * This function calculates the nTH week of a given month
 *
 *
 * @param int $m
 * @param int $d
 * @param int $y
 * @param int $w	The 1st, 2nd, 3rd, 4th or 5th week of the month. [MAY NOT USE]
 * @return date
 */
function getWeek($m,$d,$y) {
    $lastDayOfMonth = date('t',mktime(0,0,0,$m,1,$y));
    return date("W",mktime(0,0,0,$m,$d,$y)) - date("W",mktime(0,0,0,$m,1,$y)) + 1;
}

/**
 * This function will get the time stamp for a day in a specific week of the month
 *
 * @return void
 * @author Jeremiah Poisson
 **/
function getTimestampForWeek($m,$d,$y,$w) {

    $month = $m;
    $day = $d;
    $year = $y;

    $weekNum = getWeek($month,$day,$year);

    if ($weekNum < $w) {
        $diff = $w - $weekNum;
        list($year,$month,$day) = futureDate($month,$year,$day,(7 * $diff));
    } else if ($weekNum > $w) {

        $done = false;
        while (!$done) {
            list($year,$month,$day) = futureDate($month,$year,$day,7);
            $weekNum = getWeek($month,$day,$year);

            if ($weekNum == $w) { $done = true; }
        } // End while loop.

    }

    return mktime(0,0,0,$month,$day,$year);

}

/**
 * Function returns a date in the future by increasing the days
 *
 * @author Jeremiah Poisson
 * @param int $month
 * @param int $year
 * @param int $day
 * @param int $numDays
 * @return array
 */
function futureDate($month,$year,$day,$numDays) { return explode('-',date('Y-m-d',mktime(0,0,0,$month,$day+$numDays,$year))); }