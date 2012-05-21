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
 * This class defines the default value for a slot, and whether this value would
 * hardlock that slot (no-one can put a talk into that slot), softlock (anyone 
 * can propose a talk for that slot, but it won't be dynamically sorted into 
 * that slot), or not locked at all.
 * 
 * @category Object_DefaultSlotType
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Screen extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $_arrDBItems = array(
        'strScreenName' => array('type' => 'varchar', 'length' => 255),
        'dtLastSeen' => array('type' => 'datetime'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $_strDBTable = "screen";
    protected $_strDBKeyCol = "intScreenID";
    // Local Object Requirements
    protected $intScreenID = null;
    protected $strScreenName = null;
    protected $dtLastSeen = null;
    protected $lastChange = null;

    /**
     * This function overloads the normal construction. It ensures that any time
     * a new screen is initialized, the supporting "ScreenDirection" columns are
     * created as well.
     *
     * @param boolean $isCreationAction Pass this variable on to the parent class
     * 
     * @return object
     */
    public function __construct($isCreationAction = false)
    {
        $_self = parent::__construct();
        if ($isCreationAction == true) {
            $_self->setKey('strScreenName', $_SERVER['REMOTE_ADDR']);
            $_self->create();
            // When creating a new screen, create ScreenDirection objects for 
            // all rooms with a direction of "Unset"
            $arrRoomObjects = Object_Room::brokerAll();
            foreach ($arrRoomObjects as $objRoomObject) {
                $sd = new Object_ScreenDirection(true);
                $sd->setKey('intScreenID', $_self->intScreenID);
                $sd->setKey('intRoomID', $objRoomObject->getKey('intRoomID'));
                $sd->setKey('enumDirection', 'unset');
                $sd->create();
            }
        }
        return $_self;
    }

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    public function getSelf()
    {
        $_self = parent::getSelf();
        $arrDirections = Object_ScreenDirection::brokerByColumnSearch('intScreenID', $this->intScreenID);
        if ($arrDirections != false) {
            foreach ($arrDirections as $direction) {
                $_self['arrDirections'][$direction->getKey('enumDirection')][$direction->getKey('intRoomID')] = $direction;
            }
            unset($_self['arrDirections']['hidden']);
        }
        return $_self;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Screen
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Screen_Demo extends Object_Screen
{
    protected $_arrDemoData = array(
        array('intScreenID' => 1, 'strScreenName' => 'Base of Stairs', 'dtLastSeen' => ''),
        array('intScreenID' => 2, 'strScreenName' => 'Top of Stairs', 'dtLastSeen' => ''),
        array('intScreenID' => 3, 'strScreenName' => 'Outside Room 1', 'dtLastSeen' => ''),
        array('intScreenID' => 4, 'strScreenName' => 'Outside Room 2', 'dtLastSeen' => ''),
        array('intScreenID' => 5, 'strScreenName' => 'Outside Room 3', 'dtLastSeen' => '')
    );
}