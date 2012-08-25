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

$this->set('demo', 1);
$this->set('DatabaseType', 'sqlite');
$this->set('RW_DSN', dirname(__FILE__) . '/unittest.sqlite');
$this->set('RW_User', null);
$this->set('RW_Pass', null);
$this->set('RO_DSN', null);
$this->set('RO_User', null);
$this->set('RO_Pass', null);

// This is a consistent way to reset the database each run.
if (file_exists(dirname(__FILE__) . '/unittest.sqlite')) {
    unlink(dirname(__FILE__) . '/unittest.sqlite');
}
