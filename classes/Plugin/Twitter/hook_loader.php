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
        try {
            $objTwitterManager = $this->initializeTwitterHandler();
            $object->setFull(true);
            $arrTalk = $object->getSelf();
            $objTwitterManager->post($arrTalk['strTalkName'] . ' is about to start in ' . $arrTalk['arrRoom']['strRoom']);
            if ($objTwitterManager->errno() != 200) {
                throw new Exception('TwitterManager: ' . $objTwitterManager->error());
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * This function initializes the Twitter Manager code.
     *
     * @return Plugin_Twitter
     */
    protected function initializeTwitterHandler()
    {
        $objCache = Base_Cache::getHandler();
        if (! isset($objCache->arrCache['twitterManager'])) {
            include dirname(__FILE__) . '/ExternalLibraries/twitterManager.php';
            Container_Config::LoadConfig();
            $cfgCK = Object_SecureConfig::brokerByID('TwitterConsumerKey', false)->getKey('value');
            $cfgCS = Object_SecureConfig::brokerByID('TwitterConsumerSecret', false)->getKey('value');
            $cfgUK = Object_SecureConfig::brokerByID('TwitterUserKey', false)->getKey('value');
            $cfgUS = Object_SecureConfig::brokerByID('TwitterUserSecret', false)->getKey('value');
            if ($cfgCK == false || $cfgCS == false || $cfgUK == false || $cfgUS == false) {
                throw new Exception('TwitterManager: Missing config values');
            } 
            $objCache->arrCache['TwitterManager'] = new TwitterManager($cfgCK, $cfgCS, $cfgUK, $cfgUS);
        }
        return $objCache->arrCache['TwitterManager'];
    }
}