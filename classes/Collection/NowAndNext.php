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
 * This class collates all the objects needed to render a NowAndNext Page
 *
 * @category Collection_NowAndNext
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Collection_NowAndNext extends Abstract_GenericCollection
{
    /**
     * Collect the data for this collection
     *
     * @param integer|null $room The room to return, or null for all of them
     * 
     * @return object This class 
     */
    protected function __construct($room = null)
    {        
        $arrRoomObjects = Object_Room::brokerAll();
        foreach ($arrRoomObjects as $objRoom) {
            $this->_arrData['arrRooms']['room_' . $objRoom->getKey('intRoomID')] = $objRoom->getSelf();
        }
        $arrSlotObjects = Object_Slot::brokerAll();
        foreach ($arrSlotObjects as $objSlot) {
            $this->_arrData['arrSlots']['slot_' . $objSlot->getKey('intSlotID')] = $objSlot->getSelf();
        }
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefSlotTypeObj = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefSlotTypeObj as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        list($now, $next) = Object_Slot::getNowAndNext();
        foreach ($arrSlotObjects as $objSlot) {
            $this->_arrData['arrSlots']['slot_' . $objSlot->getKey('intSlotID')] = $objSlot->getSelf();
            if ($objSlot->getKey('intSlotID') == $now || $objSlot->getKey('intSlotID') == $next) {
                foreach ($arrRoomObjects as $objRoom) {
                    $this->_arrData['arrRooms']['room_' . $objRoom->getKey('intRoomID')] = $objRoom->getSelf();
                    if ($room == null || $objRoom->getKey('intRoomID') == $room) {
                        $objRoom->setFull(true);
                        if ($objSlot->getKey('intDefaultSlotTypeID') > 0) {
                            $this->_arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')] = array(
                                'strTalkTitle' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['strDefaultSlotType'], 
                                'isLocked' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['locksSlot'],
                                'arrRoom' => $objRoom->getSelf(),
                                'arrSlot' => $objSlot->getSelf(),
                                'isNow' => false,
                                'isNext' => false
                            );
                        } else {
                            $this->_arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')] = array(
                                'strTalkTitle' => '', 
                                'isLocked' => 'none',
                                'arrRoom' => $objRoom->getSelf(),
                                'arrSlot' => $objSlot->getSelf(),
                                'isNow' => false,
                                'isNext' => false
                            );
                        }
                        if ($objSlot->getKey('intSlotID') == $now) {
                            $this->_arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')]['isNow'] = true;
                        } elseif ($objSlot->getKey('intSlotID') == $next) {
                            $this->_arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')]['isNext'] = true;
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
                        $this->_arrData[$objTalk->getKey('intRoomID')][$intSlotID] = $objTalk->getSelf();
                        if ($objTalk->getKey('isSlotLocked') == 1) {
                            $this->_arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isLocked'] = 'hardlock';
                        } else {
                            $this->_arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isLocked'] = 'none';
                        }
                        if ($intSlotID == $now) {
                            $this->_arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isNow'] = true;
                        } elseif ($intSlotID == $next) {
                            $this->_arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isNext'] = true;
                        }
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * A mock up of the Object_ style of broker functions, for collections of data (not quite working the same!)
     *
     * @param string $date The date of the timetable to retrieve. Leave blank for all dates known
     * 
     * @return array
     */
    public static function brokerByID($date = null)
    {
        if ($date != null) {
            $date = date('Y-m-d', strtotime($date));
        }
        return parent::brokerByID($date);
    }
}