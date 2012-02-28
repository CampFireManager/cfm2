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
 * This class defines the object for PDO to use when retrives data about a room.
 * 
 * @category Object_Room
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Room extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strRoomName' => array('type' => 'varchar', 'length' => 255),
        'jsonResourceList' => array('type' => 'text')
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "intRoomID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intRoomID = null;
    protected $strRoomName = null;
    protected $jsonResourceList = null;
    
    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->getFull() == true) {
            $resources = (array) json_decode($this->jsonResourceList);
            foreach ($resources as $resource) {
                $objResource = Object_Resource::brokerByID($resource);
                if (is_object($objResource)) {
                    $self['arrResources'][] = $objResource->getSelf();
                }
            }
        }
        return $self;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Room
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Room_Demo extends Object_Room
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intRoomID' => 1, 'strRoomName' => 'Room A', 'jsonResourceList' => '[1,2]'),
        array('intRoomID' => 2, 'strRoomName' => 'Room B', 'jsonResourceList' => '[2,3]'),
        array('intRoomID' => 3, 'strRoomName' => 'Room C', 'jsonResourceList' => '[3]')
    );
}