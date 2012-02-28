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

class Plugin_Twitter
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
        $objCache = Base_Cache::getHandler();
        if (! isset($objCache->arrCache['twitterManager'])) {
            include dirname(__FILE__) . '/ExternalLibraries/twitterManager.php';
            $objConfig = new Base_Config();
            $objCache->arrCache['TwitterManager'] = new TwitterManager(
                $objConfig->getSecureConfig('TwitterConsumerKey'), 
                $objConfig->getSecureConfig('TwitterConsumerSecret'),
                $objConfig->getSecureConfig('TwitterUserKey'),
                $objConfig->getSecureConfig('TwitterUserSecret')
            );
        }
        $objTwitterManager = $objCache->arrCache['TwitterManager'];

        $object->setFull(true);
        $arrTalk = $object->getSelf();
        $objTwitterManager->post($arrTalk['strTalkName'] . ' is about to start in ' . $arrTalk['arrRoom']['strRoomName']);
        if ($objTwitterManager->errno() != 200) {
            error_log('TwitterManager: ' . $objTwitterManager->error());
        }
    }

}