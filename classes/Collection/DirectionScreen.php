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
 * This class collates all the objects needed to render a DirectionScreen Page
 *
 * @category Collection_DirectionScreen
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Collection_DirectionScreen extends Base_GenericCollection
{
    /**
     * Collect the data for this collection
     *
     * @param integer|null $screen The screen ID to return, or null to create a
     * new screen and reload to that page.
     * 
     * @return object This class 
     */
    protected function __construct($screen = null)
    {
        if ($screen != null) {
            $objScreen = Object_Screen::brokerByID($screen);
        }
        if ($screen == null || $objScreen == false) {
            $objScreen = new Object_Screen(true);
            $objScreen->setKey('dtLastSeen', date('Y-m-d H:i:s'));
            $objScreen->write();
            Base_Response::redirectTo('directionscreen/' . $objScreen->getPrimaryKeyValue());
        }
        $objScreen->setKey('dtLastSeen', date('Y-m-d H:i:s'));
        $objScreen->write();

        $arrRoomObjects = Object_Room::brokerAll();
        $arrRooms = array();
        foreach ($arrRoomObjects as $objRoom) {
            $objRoom->setFull(true);
            $arrRooms[$objRoom->getKey('intRoomID')] = $objRoom;
        }

        $arrScreen = $objScreen->getSelf();
        $arrScreenDirections = array(
            'upleft' => null, 
            'upcentre' => null, 
            'upright' => null, 
            'left' => null, 
            'right' => null, 
            'downleft' => null, 
            'downcentre' => null, 
            'downright' => null, 
            'inside' => null,
            'unset' => null
        );
        
        foreach ($arrScreen['arrDirections'] as $strDirection => $arrDirections) {
            ksort($arrRooms);
            foreach ($arrDirections as $intRoomID => $objDirection) {
                $arrScreenDirections[$strDirection][$objDirection->getKey('intScreenDirectionID')] = $arrRooms[$intRoomID];
            }
        }
        
        $arrSlotObjects = Object_Slot::brokerAll();
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefaultSlotTypeObjects = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefaultSlotTypeObjects as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        list($now, $next) = Object_Slot::getNowAndNext();
        foreach ($arrSlotObjects as $objSlot) {
            if ($objSlot->getKey('intSlotID') == $now || $objSlot->getKey('intSlotID') == $next) {
                $arrSlot = $objSlot->getSelf();
                foreach ($arrScreen['arrDirections'] as $strDirection => $arrRooms) {
                    foreach ($arrRooms as $intRoomID => $objRoom) {
                        if ($objSlot->getKey('intDefaultSlotTypeID') > 0) {
                            $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')] = array(
                                'strTalkTitle' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['strDefaultSlotType'], 
                                'isLocked' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['locksSlot'],
                                'arrRoom' => $objRoom->getSelf(),
                                'arrSlot' => $objSlot->getSelf(),
                                'isNow' => false,
                                'isNext' => false
                            );
                        } else {
                            $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')] = array(
                                'strTalkTitle' => '', 
                                'isLocked' => 'none',
                                'arrRoom' => $objRoom->getSelf(),
                                'arrSlot' => $objSlot->getSelf(),
                                'isNow' => false,
                                'isNext' => false
                            );
                        }
                        if ($objSlot->getKey('intSlotID') == $now) {
                            $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')]['isNow'] = true;
                        } elseif ($objSlot->getKey('intSlotID') == $next) {
                            $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')]['isNext'] = true;
                        }
                    }
                }
            }
        }
                
        if (is_array($arrTalkObjects)) {
            foreach ($arrTalkObjects as $objTalk) {
                $objTalk->setFull(true);
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    if (($now == $intSlotID || $next == $intSlotID) && ($room == null || $room == $objTalk->getKey('intRoomID'))) {
                        $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID] = $objTalk->getSelf();
                        if ($objTalk->getKey('isSlotLocked') == 1) {
                            $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID]['isLocked'] = 'hardlock';
                        } else {
                            $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID]['isLocked'] = 'none';
                        }
                        if ($intSlotID == $now) {
                            $this->arrData[$objRoom->getKey('intRoomID')][$intSlotID]['isNow'] = true;
                        } elseif ($intSlotID == $next) {
                            $this->arrData[$objRoom->getKey('intRoomID')][$intSlotID]['isNext'] = true;
                        }
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * A mock up of the Object_ style of broker functions, for collections of data
     *
     * @param string $screen The screen ID to retrieve, or null to create a new one.
     * 
     * @return array
     */
    public static function brokerByID($screen = null)
    {
        return parent::brokerByID($screen);
    }
}