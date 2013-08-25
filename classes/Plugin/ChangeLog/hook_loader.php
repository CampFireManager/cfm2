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
 * This plugin is used to verbosely log all the database queries which result
 * in changes.
 *
 * @category Plugin_ChangeLog
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Plugin_ChangeLog
{
    /**
     * This function triggers whenever a createRecord is called.
     * 
     * @param object $object The object in which the createRecord was just triggered
     * 
     * @return void
     */
    function hook_createRecord($object)
    {
        $object->writeChangeLog();
    }

    /**
     * This function triggers whenever a deleteRecord is called.
     * 
     * @param object $object The object in which the deleteRecord was just triggered
     * 
     * @return void
     */
    function hook_deleteRecord($object)
    {
        $object->writeChangeLog();
    }

    /**
     * This function triggers whenever an updateRecord is called.
     * 
     * @param object $object The object in which the updateRecord was just triggered
     * 
     * @return void
     */
    function hook_updateRecord($object)
    {
        $object->writeChangeLog();
    }
}