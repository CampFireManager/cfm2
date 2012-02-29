<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

/**
 * This value is computed to calculate the correct Database Connection String
 * @var string Initially empty, calculated once the local settings are raised.
 */
$RW_DSN = '';
/**
 * These values are the default R/W Database Connection Values
 * @var string Type of connection
 */
$RW_TYPE = 'mysql';
/**
 * These values are the default R/W Database Connection Values
 * @var string Hostname of the SQL server
 */
$RW_HOST = '127.0.0.1';
/**
 * These values are the default R/W Database Connection Values
 * @var string TCP Port for the SQL server
 */
$RW_PORT = '3306';
/**
 * These values are the default R/W Database Connection Values
 * @var string The name of the collection of tables on the Database Server
 */
$RW_BASE = 'database';
/**
 * These values are the default R/W Database Connection Values
 * @var string The default username to connect with
 */
$RW_USER = 'root';
/**
 * These values are the default R/W Database Connection Values
 * @var string The default password to connect with
 */
$RW_PASS = '';

/**
 * These values are the default R/W Database Connection Values
 * @var boolean Should the Read Only database and Read/Write conections be
 * separated?
 */
$SPLIT_RO_RW = false;

/**
 * This value is computed to calculate the correct Database Connection String
 * @var string Initially empty, calculated once the local settings are raised.
 */
$RO_DSN = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string TCP Port for the SQL server
 */
$RO_TYPE = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string Hostname of the SQL server
 */
$RO_HOST = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string TCP Port for the SQL server
 */
$RO_PORT = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string The name of the collection of tables on the Database Server
 */
$RO_BASE = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string The default username to connect with
 */
$RO_USER = '';
/**
 * These values are the default R/O Database Connection Values
 * @var string The default password to connect with
 */
$RO_PASS = '';

/**
 * These values store local application specific values - usually for programs
 * which exist locally.
 * @var array An array of application specific values.
 */
$APPCONFIG = array();

/**
 * This is the local timezone indicator
 * @var string
 */
$TZ = 'UTC';

if (file_exists(dirname(__FILE__) . "/local.php")) {
    /**
     * The referenced file is the local configuration settings.
     */
    include dirname(__FILE__) . "/local.php";
}

if ($RW_DSN == '') {
    $RW_DSN = array(
        'string' => "$RW_TYPE:host=$RW_HOST;port=$RW_PORT;dbname=$RW_BASE",
        'user' => $RW_USER,
        'pass' => $RW_PASS
    );
}

if ($RO_DSN == '' && $SPLIT_RO_RW == true) {
    $RO_DSN = array(
        'string' => "$RO_TYPE:host=$RO_HOST;port=$RO_PORT;dbname=$RO_BASE",
        'user' => $RO_USER,
        'pass' => $RO_PASS
    );
}

date_default_timezone_set($TZ);
// This code from http://www.php.net/manual/en/datetimezone.getoffset.php#105705
if (date_default_timezone_get() == 'UTC') {
    $offsetString = 'Z'; // No need to calculate offset, as default timezone is already UTC
} else {
    $phpTime = '2001-01-01 01:00:00';
    $millis = strtotime($phpTime); // Convert time to milliseconds since 1970, using default timezone
    $timezone = new DateTimeZone(date_default_timezone_get()); // Get default system timezone to create a new DateTimeZone object
    $offset = $timezone->getOffset(new DateTime($phpTime)); // Offset in seconds to UTC
    $offsetHours = round(abs($offset)/3600);
    $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
    $offsetString = ($offset < 0 ? '-' : '+') . ($offsetHours < 10 ? '0' : '') . $offsetHours . ':' . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
}
$APPCONFIG['TZ_Offset'] = $offsetString;