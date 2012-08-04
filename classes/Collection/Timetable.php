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
 * This class returns all the timetable data, sorted as talks by slot then room
 *
 * @category Collection_Timetable
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Collection_Timetable extends Abstract_GenericCollection
{
    /**
     * A mock up of the Object_ style of broker functions
     *
     * @param string $date The date of the timetable to retrieve. Leave blank for all dates known
     * 
     * @return array
     */
    protected function __construct($date = null)
    {
        if ($date != null) {
            $date = date('Y-m-d', strtotime($date));
        }
        $this->arrData['x_axis'] = 'slot';
        $this->arrData['y_axis'] = 'room';
        $arrRoomObjects = Object_Room::brokerAll();
        foreach ($arrRoomObjects as $objRoom) {
            $this->arrData['arrRooms']['room_' . $objRoom->getKey('intRoomID')] = $objRoom->getSelf();
        }
        $arrSlotObjects = Object_Slot::brokerAll();
        foreach ($arrSlotObjects as $objSlot) {
            $this->arrData['arrSlots']['slot_' . $objSlot->getKey('intSlotID')] = $objSlot->getSelf();
        }
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefSlotTypeObj = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefSlotTypeObj as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        list($now, $next) = Object_Slot::getNowAndNext();
        foreach ($arrSlotObjects as $objSlot) {
            if ($date == null 
                || $objSlot->getKey('dateStart') == $date
            ) {
                foreach ($arrRoomObjects as $objRoom) {
                    $objRoom->setFull(true);
                    if ($objSlot->getKey('intDefaultSlotTypeID') > 0) {
                        $this->arrData['arrTimetable']['slot_' . $objSlot->getKey('intSlotID')]['room_' . $objRoom->getKey('intRoomID')] = array(
                            'strTalk' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['strDefaultSlotType'], 
                            'isLocked' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['lockSlot'],
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf(),
                            'isNow' => false,
                            'isNext' => false
                        );
                    } else {
                        $this->arrData['arrTimetable']['slot_' . $objSlot->getKey('intSlotID')]['room_' . $objRoom->getKey('intRoomID')] = array(
                            'strTalk' => '', 
                            'isLocked' => 'none',
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf(),
                            'isNow' => false,
                            'isNext' => false
                        );
                    }
                    if ($objSlot->getKey('intSlotID') == $now) {
                        $this->arrData['arrTimetable']['slot_' . $objSlot->getKey('intSlotID')]['room_' . $objRoom->getKey('intRoomID')]['isNow'] = true;
                    } elseif ($objSlot->getKey('intSlotID') == $next) {
                        $this->arrData['arrTimetable']['slot_' . $objSlot->getKey('intSlotID')]['room_' . $objRoom->getKey('intRoomID')]['isNext'] = true;
                    }
                }
            }
        }
                
        if (is_array($arrTalkObjects)) {
            foreach ($arrTalkObjects as $objTalk) {
                $objTalk->setFull(true);
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    if ($intSlotID == -1) {
                        // TODO: Make this work for board-wide talks. Ignore for now until Post OGGCamp
                    } else {
                        $objSlot = $arrSlotObjects[$intSlotID];
                        if ($date == null || $objSlot->getKey('dateStart') == $date) {
                            $intRoomID = $objTalk->getKey('intRoomID');
                            if ($intRoomID == -1) {
                                $arrTalk = $objTalk->getSelf();
                                $intRoomID = 'limbo_' . ((float) $arrTalk['intAttendees'] - ($arrTalk['intTalkID'] / 1000));
                            }
                            $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID] = $objTalk->getSelf();
                            if ($objTalk->getKey('intRoomID') == -1) {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['arrRoom']['strRoom'] = 'Limbo';
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['arrRoom']['intCapacity'] = '0';
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['arrRoom']['strCapacity'] = '&infin;';
                            }
                            if ($objTalk->getKey('isSlotLocked') == 1) {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isLocked'] = 'hardlock';
                            } else {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isLocked'] = 'none';
                            }
                            if ($intSlotID == $now) {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNow'] = true;
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNext'] = false;
                            } elseif ($intSlotID == $next) {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNow'] = false;
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNext'] = true;
                            } else {
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNow'] = false;
                                $this->arrData['arrTimetable']['slot_' . $intSlotID]['room_' . $intRoomID]['isNext'] = false;
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Get the most recent lastChange in the grid
     *
     * @param string  $column  Unused
     * @param string  $value   Unused
     * @param boolean $inverse Unused
     * 
     * @return datetime
     */
    public static function lastChangeByColumnSearch($column = null, $value = null, $inverse = false)
    {
        $column = null;
        $value = null;
        $inverse = null;
        return self::lastChangeAll();
    }
    
    /**
     * Return the most recent lastChange in the grid
     *
     * @return datetime
     */
    public static function lastChangeAll()
    {
        $intRoom = strtotime(Object_Room::lastChangeAll());
        $intSlot = strtotime(Object_Slot::lastChangeAll());
        $intTalk = strtotime(Object_Talk::lastChangeAll());
        if ($intRoom >= $intSlot && $intRoom >= $intTalk) {
            return date('Y-m-d H:i:s', $intRoom);
        }
        if ($intSlot >= $intRoom && $intSlot >= $intTalk) {
            return date('Y-m-d H:i:s', $intSlot);
        }
        if ($intTalk >= $intRoom && $intTalk >= $intSlot) {
            return date('Y-m-d H:i:s', $intTalk);
        }
    }
}