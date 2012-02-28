<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category ConfigFiles
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

$RW_TYPE = 'mysql';
$RW_HOST = '127.0.0.1';
$RW_PORT = '3306';
$RW_BASE = 'database';
$RW_USER = 'root';
$RW_PASS = '';

$SPLIT_RO_RW = false;

$RO_TYPE = '';
$RO_HOST = '';
$RO_PORT = '';
$RO_BASE = '';
$RO_USER = '';
$RO_PASS = '';

$APPCONFIG = array();

if (file_exists(dirname(__FILE__) . "/local.php")) {
    include dirname(__FILE__) . "/local.php";
}

if (!isset($RW_DSN)) {
    $RW_DSN = array(
        'string' => "$RW_TYPE:host=$RW_HOST;port=$RW_PORT;dbname=$RW_BASE",
        'user' => $RW_USER,
        'pass' => $RW_PASS
    );
}

if (!isset($RO_DSN) and $SPLIT_RO_RW == true) {
    $RO_DSN = array(
        'string' => "$RO_TYPE:host=$RO_HOST;port=$RO_PORT;dbname=$RO_BASE",
        'user' => $RO_USER,
        'pass' => $RO_PASS
    );
}