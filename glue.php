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

$arrGlues = Collection_Glue::brokerAll();
do {
    foreach ($arrGlues as $objGlue) {
        $objGlue->follow_followers();
        $objGlue->read_private();
        $objGlue->read_public();
        $objGlue->send();
    }
} while ($forever);