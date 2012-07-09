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
 * This is the local timezone indicator
 * @var string
 */
$TZ = 'Europe/London';

if (file_exists(dirname(__FILE__) . "/local.php")) {
    /**
     * The referenced file is the local configuration settings.
     */
    include dirname(__FILE__) . "/local.php";
}

$this->set('DatabaseType', $RW_TYPE);
$this->set('RO_DSN', null);
$this->set('RO_User', null);
$this->set('RO_Pass', null);

if ($RW_TYPE != 'sqlite') {
    $this->set('RW_DSN', "host=$RW_HOST;port=$RW_PORT;dbname=$RW_BASE");
    $this->set('RW_User', $RW_USER);
    $this->set('RW_Pass', $RW_PASS);
} else {
    $this->set('RW_DSN', dirname(__FILE__) . '/' . $RW_BASE . '.sqlite');
    $this->set('RW_User', null);
    $this->set('RW_Pass', null);
    $SPLIT_RO_RW = false;
}

if ($SPLIT_RO_RW == true) {
    $this->set('RO_DSN', "host=$RO_HOST;port=$RO_PORT;dbname=$RO_BASE");
    $this->set('RO_User', $RO_USER);
    $this->set('RO_Pass', $RO_PASS);
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
$this->set('TZ', $offsetString);