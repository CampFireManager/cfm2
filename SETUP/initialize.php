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
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

/**
 * This file defines the autoloader for the classes mentioned elsewhere.
 */
require_once dirname(__FILE__) . '/../classes/autoloader.php';
$objRequest = Container_Request::getRequest();
if ($objRequest->get_strRequestMethod() != 'file') {
    die("Must only be run from the command line.");
}
Object_User::isSystem(true);
Container_Config_Demo::initializeDemo();

foreach (new DirectoryIterator(dirname(__FILE__) . '/../classes/Object') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $classname = 'Object_' . $file->getBasename('.php');
        echo "Initializing $classname\r\n";
        Object_User::isSystem(true);
        $class = new $classname();
        $class->initialize();
    }
}