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
 * @category Plugin_LimboTalks
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Plugin_LimboTalks
{
    /**
     * This function triggers each cronTick.
     * 
     * @return void
     */
    function hook_cronTick()
    {
        Object_User::isSystem(true);
        $talks = Object_Talk::brokerByColumnSearch('isLocked', null);
        $sort_array = array();
        $min_votes = Container_Config::brokerByID('LimboMinimumVotes', 1)->getKey('value');
        foreach ($talks as $talk) {
            $talk->setFull(true);
            $data = $talk->getSelf();
            if ($min_votes > $data['intAttendees']) {
                $talk->unschedule();
            }
        }
    }
}