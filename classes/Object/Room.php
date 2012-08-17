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

class Object_Room extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strRoom' => array('type' => 'varchar', 'length' => 255, 'required' => 'admin', 'render_in_sub_views' => true),
        'jsonResourceList' => array('type' => 'text', 'optional' => 'admin', 'source' => 'Resource', 'array' => 1),
        'intCapacity' => array('type' => 'integer', 'length' => 4, 'required' => 'admin'),
        'isLocked' => array('type' => 'tinyint', 'length' => 1, 'required' => 'admin', 'default_value' => 0),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_strRoom' => array('en' => 'Room'),
        'label_intCapacity' => array('en' => 'Capacity'),
        'label_jsonResourceList' => array('en' => 'Items available in this room'),
        'label_isLocked' => array('en' => 'Room reserved for specific talks'),
        'label_new_isLocked' => array('en' => 'Lock room')
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "intRoomID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intRoomID = null;
    protected $strRoom = null;
    protected $jsonResourceList = null;
    protected $intCapacity = null;
    protected $isLocked = false;
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
        if ($self['intCapacity'] == 0) {
            $self['strCapacity'] = '&infin;';
        } else {
            $self['strCapacity'] = $self['intCapacity'];
        }
        if ($this->isFull() == true) {
            $resources = json_decode($this->jsonResourceList, true);
            if (is_array($resources) && count($resources) > 0) {
                foreach ($resources as $resource) {
                    $objResource = Object_Resource::brokerByID($resource);
                    if (is_object($objResource)) {
                        $arrResource = $objResource->getSelf();
                        $self['arrResources'][] = $arrResource;
                        if ($arrResource['epochLastChange'] > $self['epochLastChange']) {
                            $self['epochLastChange'] = $arrResource['epochLastChange'];
                        }
                    }
                }
            }
        }
        Base_Response::setLastModifiedTime($self['epochLastChange']);
        $self['lastChange'] = date('Y-m-d H:i:s', $self['epochLastChange']);
        return $self;
    }
    
    /**
     * This function returns the array of rooms, sorted by the capacity of the
     * room.
     *
     * @return array 
     */
    public static function brokerAllByRoomSize()
    {
        $arrRooms = Object_Room::brokerAll();
        $arrRoomsByCapacity = array();
        // Sort out the room sizes
        foreach ($arrRooms as $objRoom) {
            $arrRoomsByCapacity[$objRoom->getKey('intCapacity') - ($objRoom->getKey('intRoomID') / 1000)] = $objRoom;
        }
        krsort($arrRoomsByCapacity);
        $roomsize = 0;
        foreach ($arrRoomsByCapacity as $objRoom) {
            $arrRoomsBySize[$roomsize++] = $objRoom;
        }
        return $arrRoomsBySize;
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
    protected $arrDemoData = array(
        array('intRoomID' => 1, 'strRoom' => 'Room A', 'intCapacity' => 100, 'isLocked' => 1, 'jsonResourceList' => '[1,2]'),
        array('intRoomID' => 2, 'strRoom' => 'Room B', 'intCapacity' => 50, 'jsonResourceList' => '[2,3]'),
        array('intRoomID' => 3, 'strRoom' => 'Room C', 'intCapacity' => 75, 'jsonResourceList' => '[3]')
    );
}
