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
    protected $arrDBItems = array(
        'strTrack' => array('type' => 'varchar', 'length' => 255, 'required' => 'admin', 'render_in_sub_views' => true),
        'rgbColour' => array('type' => 'varchar', 'length' => 6, 'required' => 'admin'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "track";
    protected $strDBKeyCol = "intTrackID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intTrackID = null;
    protected $strTrack = null;
    protected $rgbColour = null;
    protected $lastChange = null;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getData()
    {
        $self = parent::getData();
        if ($this->booleanFull) {
            $arrTalks = Object_Talk::brokerByColumnSearch('intTrackID', $this->intTrackID);
            if ($arrTalks != false) {
                foreach ($arrTalks as $objTalk) {
                    $arrTalk = $objTalk->getSelf();
                    $self['arrTalks'][] = $arrTalk;
                    if ($arrTalk['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $arrTalk['epochLastChange'];
                    }
                }
            }
        }
        Base_Response::setLastModifiedTime($self['epochLastChange']);
        $self['lastChange'] = date('Y-m-d H:i:s', $self['epochLastChange']);
        return $self;
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
    protected $arrDemoData = array(
        array('intTrackID' => 1, 'strTrack' => 'Coding'),
        array('intTrackID' => 1, 'strTrack' => 'Novice')
    );
}