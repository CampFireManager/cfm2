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
 * This class defines the default value for a slot, and whether this value would
 * hardlock that slot (no-one can put a talk into that slot), softlock (anyone 
 * can propos a talk for that slot, but it won't be dynamically sorted into that
 * slot), or not locked at all.
 * 
 * @category Object_DefaultSlotType
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_ScreenDirection extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'intScreenID' => array('type' => 'int', 'length' => 11, 'unique' => true, 'render_in_sub_views' => true, 'required' => 'admin', 'source' => 'Screen'),
        'intRoomID' => array('type' => 'int', 'length' => 11, 'unique' => true, 'render_in_sub_views' => true, 'required' => 'admin', 'source' => 'Room'),
        'enumDirection' => array('type' => 'enum', 'options' => array('upleft', 'upcentre', 'upright', 'left', 'right', 'downleft', 'downcentre', 'downright', 'inside', 'hidden', 'unset'), 'render_in_sub_views' => true, 'required' => 'admin'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_intScreenID' => array('en' => 'Screen Name'),
        'label_intRoomID' => array('en' => 'Room'),
        'label_enumDirection' => array('en' => 'Direction from room to screen')
    );
    protected $strDBTable = "screendirection";
    protected $strDBKeyCol = "intScreenDirectionID";
    // Local Object Requirements
    protected $intScreenDirectionID = null;
    protected $intScreenID = null;
    protected $intRoomID = null;
    protected $enumDirection = null;
    protected $lastChange = null;
    
    protected function getCurrent($return)
    {
        $return = parent::getCurrent($return);
        $return['current']['enumDirection']['key'] = $this->enumDirection;
        $return['current']['enumDirection']['value'] = $this->enumDirection;
        return $return;
    }
    
    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getData()
    {
        $self = parent::getData();
        if ($this->isFull() == true) {
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
                    $objRoom->setFull(true);
                    $self['arrRoom'] = $objRoom->getSelf();
                    if ($self['arrRoom']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrRoom']['epochLastChange'];
                    }
                }
            }
            if ($this->intScreenID != null && $this->intScreenID > 0) {
                $objScreen = Object_Screen::brokerByID($this->intScreenID);
                if (is_object($objScreen)) {
                    $objScreen->setFull(true);
                    $self['arrScreen'] = $objScreen->getSelf();
                    if ($self['arrScreen']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrScreen']['epochLastChange'];
                    }
                }
            }
        }
        return $self;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Screen
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
class Object_ScreenDirection_Demo extends Object_ScreenDirection
{
    protected $arrDemoData = array(
        array('intScreenDirectionID' => 1, 'intScreenID' => 1, 'intRoomID' => 1, 'enumDirection' => 'upcentre'),
        array('intScreenDirectionID' => 2, 'intScreenID' => 1, 'intRoomID' => 2, 'enumDirection' => 'upcentre'),
        array('intScreenDirectionID' => 3, 'intScreenID' => 1, 'intRoomID' => 3, 'enumDirection' => 'left'),
        array('intScreenDirectionID' => 4, 'intScreenID' => 2, 'intRoomID' => 1, 'enumDirection' => 'left'),
        array('intScreenDirectionID' => 5, 'intScreenID' => 2, 'intRoomID' => 2, 'enumDirection' => 'right'),
        array('intScreenDirectionID' => 6, 'intScreenID' => 2, 'intRoomID' => 3, 'enumDirection' => 'downleft'),
        array('intScreenDirectionID' => 7, 'intScreenID' => 3, 'intRoomID' => 1, 'enumDirection' => 'inside'),
        array('intScreenDirectionID' => 8, 'intScreenID' => 3, 'intRoomID' => 2, 'enumDirection' => 'right'),
        array('intScreenDirectionID' => 9, 'intScreenID' => 3, 'intRoomID' => 3, 'enumDirection' => 'right'),
        array('intScreenDirectionID' => 10, 'intScreenID' => 4, 'intRoomID' => 1, 'enumDirection' => 'left'),
        array('intScreenDirectionID' => 11, 'intScreenID' => 4, 'intRoomID' => 2, 'enumDirection' => 'inside'),
        array('intScreenDirectionID' => 12, 'intScreenID' => 4, 'intRoomID' => 3, 'enumDirection' => 'left'),
        array('intScreenDirectionID' => 13, 'intScreenID' => 5, 'intRoomID' => 1, 'enumDirection' => 'right'),
        array('intScreenDirectionID' => 14, 'intScreenID' => 5, 'intRoomID' => 2, 'enumDirection' => 'right'),
        array('intScreenDirectionID' => 15, 'intScreenID' => 5, 'intRoomID' => 3, 'enumDirection' => 'inside')
    );
}
