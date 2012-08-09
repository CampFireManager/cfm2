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
 * This file defines the autoloader for the classes mentioned elsewhere.
 */
require_once dirname(__FILE__) . '/classes/autoloader.php';
Container_Config::LoadConfig();
$objRequest = Container_Request::getRequest();
if ($objRequest->get_strRequestMethod() != 'file') {
    die("Must only be run from the command line.");
}

$forever = true;
foreach ($objRequest->get_arrRqstParameters() as $key => $parameter) {
    if ($key == '--once' || $parameter == '--once') {
        $forever = false;
    }
}
Object_User::isSystem(true);
$arrGlues = Glue_Broker::brokerAll();
do {
    echo "About to run glues at " . date('Y-m-d H:i:s') . "\r\n";

    foreach ($arrGlues as $objGlue) {
        echo "Glue: " . $objGlue->getGlue() . "\r\n";
        echo " * Following followers: ";
        $objGlue->follow_followers();
        echo "Done\r\n";
        echo " * Reading private messages: ";
        $objGlue->read_private();
        echo "Done\r\n";
        echo " * Reading public messages: ";
        $objGlue->read_public();
        echo "Done\r\n";
        echo " * Sending messages: ";
        $objGlue->send();
        echo "Done\r\n";
    }
    echo "Sleeping to reduce server load: ";
    if ($forever) {
        sleep(Container_Config::brokerByID('Sleep In Cron Script', '5')->getKey('value'));
    }
    echo "Awake\r\n";
    if ($forever) {
        echo "\r\n=========================================\r\n\r\n";
    }
} while ($forever);