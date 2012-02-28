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

class Object_Track extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strTrackName' => array('type' => 'varchar', 'length' => 255)
    );
    protected $strDBTable = "track";
    protected $strDBKeyCol = "intTrackID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intTrackID = null;
    protected $strTrackName = null;
    // Post-processing Properties
    protected $arrTalks = null;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->booleanFull) {
            if ($this->arrTalks == null) {
                $this->arrTalks = Object_Talk::brokerByColumnSearch('intTrackID', $this->intTrackID);
            }
            if ($this->arrTalks == null) {
                foreach ($this->arrTalks as $talk) {
                    $self['arrTalks'][] = $talk->getSelf();
                }
            }
        }
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
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intTrackID' => 1, 'strTrackName' => 'Coding'),
        array('intTrackID' => 1, 'strTrackName' => 'Novice')
    );
}