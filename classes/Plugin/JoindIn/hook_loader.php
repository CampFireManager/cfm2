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
 * This plugin is used to publish the talks to Joind.in on fixing
 *
 * @category Plugin_JoindIn
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @author   Lorna Mitchell <her@lornajane.net>
 * @license  about:blank?tbc tbc
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Plugin_JoindIn
{
    function hook_cronTick()
    {
        $arrTalks = Object_Talk::brokerAll();
        $arrNowNext = Object_Slot::getNowAndNext();
        $intNow = $arrNowNext[0];
        foreach ($arrTalks as $objTalk) {
            if ($objTalk->getKey('intSlotID') <= $intNow 
                && $objTalk->getKey('isLocked') == "1" 
                && $objTalk->getKey('intRoomID') != '-1'
                && ! strpos($objTalk->getKey('jsonLinks'), 'joind.in')
            ) {
                $this->hook_fixTalk($objTalk);
            }
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

            $oAuth_UserToken = Object_SecureConfig::brokerByID('Plugin_JoindIn_Token');
            if ($oAuth_UserToken != false) {
                $oAuth_UserToken = $oAuth_UserToken->getKey('value');
            }
            $strJoindInAPI = Object_SecureConfig::brokerByID('Plugin_JoindIn_API');
            if ($strJoindInAPI != false) {
                $strJoindInAPI = $strJoindInAPI->getKey('value');
            }
            $intEventID = Object_SecureConfig::brokerByID('Plugin_JoindIn_Event');
            if ($intEventID != false) {
                $intEventID = $intEventID->getKey('value');
            }
            
            if ($oAuth_UserToken == false || $strJoindInAPI == false || $intEventID == false) {
                return false;
            }

            // set your own data as follows
            $date = new DateTime($arrTalk['arrSlot']['dateStart'] . ' ' . $arrTalk['arrSlot']['timeStart'], new DateTimeZone('Europe/London'));
            $presenters = array();
            foreach ($arrTalk['arrPresenters'] as $arrPresenter) {
                $presenters[] = $arrPresenter['strUser'];
            }
            if ($arrTalk['strTalkSummary'] == '') {
                $arrTalk['strTalkSummary'] = 'A talk.';
            }
            $talk_data = array(
                'talk_title' => $arrTalk['strTalk'], 
                'talk_description' => $arrTalk['strTalkSummary'],
                'start_date' => $date->format('c'),
                'speakers' => $presenters,
            );

            $path = $strJoindInAPI . '/v2.1/events/' . $intEventID . '/talks';
            
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: OAuth ' . $oAuth_UserToken));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($talk_data));
            curl_setopt($ch, CURLOPT_HEADER, false);
            $curl = curl_exec($ch);

            $data = json_decode($curl, true);
 
            if ($data != false && isset($data['talks'])) {
                foreach ($data['talks'] as $talk) {
                    if (isset($talk['website_uri'])) {
                        $talk_url = $talk['website_uri'];
                    }
                }
            }

            Object_User::isSystem(true);
            $object->setKey('jsonLinks', Base_GeneralFunctions::addJson($object->getKey('jsonLinks'), 'Joind.in', $talk_url));
            $object->write();
            Object_User::isSystem($system_state);
        } catch (Exception $e) {
            error_log($e->getMessage());
            Object_User::isSystem($system_state);
        }
    }
}