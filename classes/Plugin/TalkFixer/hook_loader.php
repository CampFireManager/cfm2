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
 * This plugin is used to de-allocate rooms and slots when a talk falls under a
 * predefined number of attendees.
 *
 * @category Plugin_TalkFixer
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Plugin_TalkFixer
{
    /**
     * This function triggers each cronTick.
     * 
     * @param string $strNow Unit Testing Purposes Only! Set what time the talks
     * are to be sorted by.
     * 
     * @return void
     */
    function hook_cronTick($strNow = null)
    {
        Object_User::isSystem(true);
        Object_Talk::lockTalks($strNow);
    }
}