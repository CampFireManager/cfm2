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
 * This plugin is used clear down any cleartext passwords, two minutes after
 * they are set.
 *
 * @category Plugin_ResetCleartexts
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Plugin_ResetCleartexts
{
    /**
     * This function triggers each cronTick.
     * 
     * @return void
     */
    function hook_cronTick()
    {
        $userauths = Object_Userauth::brokerByColumnSearch('tmpCleartext', '%');
        if (is_array($userauths) and count($userauths) > 0) {
            foreach ($userauths as $userauth) {
                $userauth->getCleartext();
            }
        }
    }
}