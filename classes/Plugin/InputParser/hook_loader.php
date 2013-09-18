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
 * This plugin is used to interact with the text-only interfaces. To have a
 * service interact with this, drop a row into the table Input with the action
 * to run, and then return the result into the table Output with the message
 * to reply.
 *
 * @category Plugin_InputParser
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Plugin_InputParser
{
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
            $arrInput = Object_Input::brokerByColumnSearch('isActioned', '0');
            foreach ($arrInput as $objInput) {
                $objUser = Object_User::brokerByCodeOnly($objInput);
                $strMessage = '';
                if (isset($objUser->objUserAuthTemp) && is_object($objUser->objUserAuthTemp)) {
                    $strMessage = ' Web access: ' . Container_Config::brokerByID('Public_URL', 'talks.oggcamp.org')->getKey('value') . ', login with Authcode: ' . $objUser->objUserAuthTemp->getKey('tmpCleartext');
                    $objUser->objUserAuthTemp->setKey('tmpCleartext', null);
                    $objUser->objUserAuthTemp->write();
                }
                switch (1) {
                case preg_match('/^([Ii][Dd][Ee][Nn][Tt][Ii][Ff][Yy])\s+(.*)\s+(\S+@\S+\.\S+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ii]\s+[Aa][Mm])\s+(.*)\s+(\S+@\S+\.\S+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ii])\s+(.*)\s+(\S+@\S+\.\S+)$/', $objInput->getKey('textMessage'), $match):
                    $objUser->setKey('strUser', $match[2]);
                    $objUser->setKey('jsonLinks', Base_GeneralFunctions::addJson($objUser->getKey('jsonLinks'), 'email', $match[3]));
                    $objUser->write();
                    Object_Output::replyToInput($objInput, 'Thanks for letting us know your name and e-mail address.' . $strMessage);
                    break;
                case preg_match('/^([Ii][Dd][Ee][Nn][Tt][Ii][Ff][Yy])\s+(.*)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ii]\s+[Aa][Mm])\s+(.*)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ii])\s+(.*)$/', $objInput->getKey('textMessage'), $match):
                    $objUser->setKey('strUser', $match[2]);
                    $objUser->write();
                    Object_Output::replyToInput($objInput, 'Thanks for letting us know your name.' . $strMessage);
                    break;
                case preg_match('/^([Aa][Tt][Tt][Ee][Nn][Dd])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Gg][Oo][Tt][Oo])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Gg][Oo])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Gg])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Aa])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                    $objTalk = Object_Talk::brokerByID($match[2]);
                    if ($objTalk != false) {
                        $strTalk = substr($objTalk->getKey('strTalk'), 0, 60);
                        if ($strTalk != $objTalk->getKey('strTalk')) {
                            $strTalk .= '...';
                        }
                        $strTalk = '"' . $strTalk . '"';
                        if (! Object_Attendee::isAttending($match[2])) {
                            $objAttendee = new Object_Attendee();
                            $objAttendee->setKey('intUserID', $objUser->getKey('intUserID'));
                            $objAttendee->setKey('intTalkID', $match[2]);
                            $objAttendee->create();
                            Object_Output::replyToInput($objInput, "OK, you're down to attend the talk $strTalk." . $strMessage);
                        } else {
                            Object_Output::replyToInput($objInput, "You had already said you were attending talk $strTalk.");
                        }
                    } else {
                        Object_Output::replyToInput($objInput, "Sorry, that talk doesn't exist.$strMessage");
                    }
                    break;
                case preg_match('/^([Dd][Ee][Cc][Ll][Ii][Nn][Ee])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Cc][Aa][Nn][Cc][Ee][Ll])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Rr][Ee][Ff][Uu][Ss][Ee])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ll][Ee][Aa][Vv][Ee])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Rr])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Cc])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Dd])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Ll])\s+(\d+)$/', $objInput->getKey('textMessage'), $match):
                    $objTalk = Object_Talk::brokerByID($match[2]);
                    if ($objTalk != false) {
                        $strTalk = substr($objTalk->getKey('strTalk'), 0, 20);
                        if ($strTalk != $objTalk->getKey('strTalk')) {
                            $strTalk .= '...';
                        }
                        $strTalk = '"' . $strTalk . '"';
                        $objAttendee = Object_Attendee::isAttending($match[2]);
                        if ($objAttendee != false) {
                            $objAttendee->delete();
                            Object_Output::replyToInput($objInput, "OK, we've acknowledged you don't want to go to $strTalk anymore." . $strMessage);
                        } else {
                            Object_Output::replyToInput($objInput, "We didn't have recorded that you would be attending $strTalk.");
                        }
                    } else {
                        Object_Output::replyToInput($objInput, "Sorry, that talk doesn't exist.$strMessage");
                    }
                    break;
                case preg_match('/^([Aa][Ss][Ss][Oo][Cc][Ii][Aa][Tt][Ee])\s+(\S+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Mm][Ee][Rr][Gg][Ee])\s+(\S+)$/', $objInput->getKey('textMessage'), $match):
                case preg_match('/^([Aa])\s+(\S+)$/', $objInput->getKey('textMessage'), $match):
                    $arrUserAuth = Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $match[2]));
                    foreach ($arrUserAuth as $objUserAuth) {
                        if ($objUserAuth->getKey('enumAuthType') == 'codeonly') {
                            $objUser->merge($objUserAuth);
                            Object_Output::replyToInput($objInput, 'OK, you can now act as ' . $objUser->getKey('strUser') . ' from this device.', $strMessage);
                        }
                    }
                    break;
                case preg_match('/^([Ww][Hh][Aa][Tt]\s+[Ii][Ss]\s+[Oo][Nn]\s+[Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\'[Ss]\s+[Oo][Nn]\s+[Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss]\s+[Oo][Nn]\s+[Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\'[Ss][Oo][Nn][Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss][Oo][Nn][Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\'[Ss]\s+[Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss]\s+[Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\s+[Ii][Ss]\s+[Oo][Nn])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\'[Ss]\s+[Oo][Nn])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss]\s+[Oo][Nn])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss][Oo][Nn])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt]\'[Ss])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt][Ss])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww][Hh][Aa][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Nn][Ee][Xx][Tt])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Ww])$/', $objInput->getKey('textMessage')):
                case preg_match('/^([Nn])$/', $objInput->getKey('textMessage')):
                    $arrNextTalks = Collection_NowAndNext::brokerAll();
                    $return = '';
                    foreach ($arrNextTalks as $objTalk) {
                        $objTalk->setFull(true);
                        $arrTalk = $objTalk->getSelf();
                        if ($arrTalk['isNext'] == true) {
                            if ($return != '') {
                                $return .= ', ';
                            }
                            $return .= '"' . substr($arrTalk['strTalk'], 0, 15);
                            if (strlen($arrTalk['strTalk']) > 15) {
                                $return .= '... ';
                            }
                            $return .=  '" in ' . $arrTalk['arrRoom']['strRoom'];
                        }
                    }
                    Object_Output::replyToInput($objInput, 'On next: ' . $return);
                    break;
                case preg_match('/^([Mm])$/', $objInput->getKey('textMessage')):
                    $arrAttend = Object_Attendee::brokerByColumnSearch('intUserID', $objUser->getKey('intUserID'));
                    $return = '';
                    $counter = 0;
                    $next = false;
                    foreach ($arrAttend as $objAttendee) {
                        $objAttendee->setFull(true);
                        $arrAttendee = $objAttendee->getSelf();
                        $arrTalk[$arrAttendee['arrTalk']['intSlotID']] = $arrAttendee['arrTalk'];
                    }
                    ksort($arrTalk);
                    foreach ($arrTalk as $arrTalkInfo) {
                        if ($arrTalkInfo['isNext'] == true || $next == true || $counter < 4) {
                            $next = true;
                            $counter++;
                            if ($return != '') {
                                $return .= ', ';
                            }
                            $return .= '"' . substr($arrTalkInfo['strTalk'], 0, 15);
                            if (strlen($arrTalkInfo['strTalk']) > 15) {
                                $return .= '... ';
                            }
                            $return .=  '" at ' . substr($arrTalkInfo['arrSlot']['timeStart'], 0, 5);
                        }
                    }
                    Object_Output::replyToInput($objInput, "Next three talks I'm attending: $return");
                    break;
                default:
                    if ($strMessage != '') {
                        Object_Output::replyToInput($objInput, "Send: 'A 1' Attend Talk ID 1. Cancel it with 'C 1'. On next? Send 'W'. Your next 3 talks to attend - Send 'M'. " . $strMessage);
                    } else {
                        Object_Output::replyToInput($objInput, "Send: 'A 1' Attend Talk ID 1. Cancel it with 'C 1'. On next? Send 'W'. Your next 3 talks to attend - Send 'M'. Or use " . Container_Config::brokerByID('Public_URL', 'talks.oggcamp.org')->getKey('value'));
                    }
                }
                $objInput->setKey('isActioned', '1');
                $objInput->write();
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}