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
 * This class defines the object for PDO to use when retrives data about a talk.
 * 
 * @category Object_Track
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Track extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $_arrDBItems = array(
        'strTrackName' => array('type' => 'varchar', 'length' => 255),
        'rgbColour' => array('type' => 'varchar', 'length' => 6),
        'lastChange' => array('type' => 'datetime')
    );
    protected $_strDBTable = "track";
    protected $_strDBKeyCol = "intTrackID";
    protected $_reqAdminToMod = true;
    // Local Object Requirements
    protected $intTrackID = null;
    protected $strTrackName = null;
    protected $rgbColour = null;
    protected $lastChange = null;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $_self = parent::getSelf();
        if ($this->_booleanFull) {
            $arrTalks = Object_Talk::brokerByColumnSearch('intTrackID', $this->intTrackID);
            if ($arrTalks != false) {
                foreach ($arrTalks as $objTalk) {
                    $arrTalk = $objTalk->getSelf();
                    $_self['arrTalks'][] = $arrTalk;
                    if ($arrTalk['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $arrTalk['lastChange'];
                    }
                }
            }
        }
        return $_self;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Track
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Track_Demo extends Object_Track
{
    protected $_arrDemoData = array(
        array('intTrackID' => 1, 'strTrackName' => 'Coding'),
        array('intTrackID' => 1, 'strTrackName' => 'Novice')
    );
}