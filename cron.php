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
do {
    echo "About to run cron tasks at " . date('Y-m-d H:i:s') . "\r\n";
    echo "(1/5) Flushing cache: ";
    Base_Cache::flush();
    echo "Done\r\n";
    echo "(2/5) Loading config: ";
    Container_Config::LoadConfig();
    echo "Done\r\n";
    echo "(3/5) Sleeping to reduce server load: ";
    sleep(Container_Config::brokerByID('Sleep In Cron Script', '5')->getKey('value'));
    echo "Awake\r\n";
    echo "(4/5) Loading hook processes: ";
    $hook = new Base_Hook();
    $hook->Load('plugin.php');
    echo "Done\r\n";
    echo "(5/5) Triggering cron hooks: \r\n";
    $hook->triggerHook('cronTick');
    echo "Done\r\n";
    if ($forever) {
        echo "==========================\r\n\r\n";
    }
} while ($forever);
