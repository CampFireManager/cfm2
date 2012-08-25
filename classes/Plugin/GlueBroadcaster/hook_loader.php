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
 * This plugin is used to broadcast to twitter when talks are about to start.
 *
 * @category Plugin_Twitter
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Plugin_GlueBroadcaster
{
    /**
     * This function sends a broadcast when the talk is about to start.
     * 
     * @param object $object The talk object that is about to start
     * 
     * @return void
     */
    function hook_talkStart($object)
    {
        $system_state = Object_User::isSystem();
        try {
            $object->setFull(true);
            $arrTalk = $object->getSelf();
            $text = $arrTalk['strTalk'] . ' is about to start in ' . $arrTalk['arrRoom']['strRoom'];

            Object_User::isSystem(true);
            $arrGlues = Glue_Broker::brokerAll();
            foreach ($arrGlues as $objGlue) {
                $strInterface = $objGlue->canSendBroadcast();
                if ($strInterface != false) {
                    $output = new Object_Output();
                    $output->setKey('textMessage', $text);
                    $output->setKey('strInterface', $strInterface);
                    $output->create();
                }
            }
            Object_User::isSystem($system_state);
        } catch (Exception $e) {
            error_log($e->getMessage());
            Object_User::isSystem($system_state);
        }
    }

    /**
     * This function sends a broadcast when the talk is fixed.
     * 
     * @param object $object The talk object that has been fixed
     * 
     * @return void
     */
    function hook_fixTalk($object)
    {
        $system_state = Object_User::isSystem();
        try {
            $object->setFull(true);
            $arrTalk = $object->getSelf();
            $text = $arrTalk['strTalk'] . ' has been fixed in room: ' . $arrTalk['arrRoom']['strRoom'] . ' to start at ' . date('H:i', $arrTalk['arrSlot']['epochStart']);

            Object_User::isSystem(true);
            $arrGlues = Glue_Broker::brokerAll();
            foreach ($arrGlues as $objGlue) {
                $strInterface = $objGlue->canSendBroadcast();
                if ($strInterface != false) {
                    $output = new Object_Output();
                    $output->setKey('textMessage', $text);
                    $output->setKey('strInterface', $strInterface);
                    $output->create();
                }
            }
            Object_User::isSystem($system_state);
        } catch (Exception $e) {
            error_log($e->getMessage());
            Object_User::isSystem($system_state);
        }
    }

    /**
     * This function performs all the input parsing requests from text-only 
     * services.
     * 
     * @return void
     */
    function hook_cronTick()
    {
        try {
            Base_Cache::flush();
            Object_User::isSystem(true);
            $arrGlues = Glue_Broker::brokerAll();
            if (count($arrGlues) > 0) {
                echo "\r\n";
            }
            foreach ($arrGlues as $objGlue) {
                echo "       - Glue: " .$objGlue->getGlue() . "\r\n";
                echo "         * Following followers: ";
                $objGlue->follow_followers();
                echo "Done\r\n";
                echo "         * Reading private messages: ";
                $objGlue->read_private();
                echo "Done\r\n";
                echo "         * Reading public messages: ";
                $objGlue->read_public();
                echo "Done\r\n";
                echo "         * Sending messages: ";
                $objGlue->send();
                echo "Done\r\n";
            }
            if (count($arrGlues) > 0) {
                echo "     + " . get_class($this) . ": ";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}