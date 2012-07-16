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
 * This class defines the object for PDO to use when retrives data about a Attendee.
 * 
 * @category Object_Attendee
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Attendee extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'intUserID' => array('type' => 'integer', 'length' => 11, 'unique' => true),
        'intTalkID' => array('type' => 'integer', 'length' => 11, 'unique' => true),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "attendee";
    protected $strDBKeyCol = "intAttendeeID";
    protected $reqCreatorToMod = true;
    // Local Object Requirements
    protected $intAttendeeID = null;
    protected $intUserID = null;
    protected $intTalkID = null;
    protected $lastChange = null;
    
    /**
     * Check to see whether the specified user is attending the talk.
     *
     * @param integer $intTalkID The talkID
     * @param integer $intUserID (optional) The userID. If this is null, broker
     * for the current user.
     * 
     * @return boolean|object
     */
    public static function isAttending($intTalkID, $intUserID = null)
    {
        if ($intUserID == null) {
            $intUserID = Object_User::brokerCurrent()->getKey('intUserID');
        }
        $arrAttending = self::brokerByColumnSearch('intUserID', $intUserID);
        foreach ($arrAttending as $objAttending) {
            if ($objAttending->getKey('intTalkID') == $intTalkID) {
                return $objAttending;
            }
        }
        return false;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Attendee
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Attendee_Demo extends Object_Attendee
{
    protected $arrDemoData = array(
        array('intUserID' => '2', 'intTalkID' => '1'),
        array('intUserID' => '3', 'intTalkID' => '1'),
        array('intUserID' => '4', 'intTalkID' => '1'),
        array('intUserID' => '1', 'intTalkID' => '3'),
        array('intUserID' => '4', 'intTalkID' => '3')
    );
}
