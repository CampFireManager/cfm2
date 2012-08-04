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
 * This plugin is used to broadcast to twitter when talks are about to start.
 *
 * @category Plugin_Twitter
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
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
            $text = $arrTalk['strTalkName'] . ' is about to start in ' . $arrTalk['arrRoom']['strRoom'];

            Object_User::isSystem(true);
            $arrGlues = Collection_Glue::brokerAll();
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
    function hook_talkFixed($object)
    {
        $system_state = Object_User::isSystem();
        try {
            $object->setFull(true);
            $arrTalk = $object->getSelf();
            $text = $arrTalk['strTalkName'] . ' has been fixed in room: ' . $arrTalk['arrRoom']['strRoom'] . ' to start at ' . date('H:i', $arrTalk['arrSlot']['epochStart']);

            Object_User::isSystem(true);
            $arrGlues = Collection_Glue::brokerAll();
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

}