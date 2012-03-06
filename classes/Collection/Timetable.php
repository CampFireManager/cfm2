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
 * This class collates all the objects needed to render a full timetable
 *
 * @category Collection_Timetable
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Collection_Timetable extends Base_GenericCollection
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
        if (Object_Room::countAll() > Object_Slot::countAll()) {
            $self = new Collection_TimetableBySlotRoom($date);
        } else {
            $self = new Collection_TimetableByRoomSlot($date);
        }
        $this->arrData = $self->arrData;
        return $this;
    }
}

class Collection_TimetableByRoomSlot extends Collection_Timetable
{
    /**
     * Collect the data for this collection
     *
     * @param integer|null $date The date to return, or everything if null
     * 
     * @return object This class 
     */
    public function __construct($date = null)
    {
        $arrRoomObjects = Object_Room::brokerAll();
        foreach ($arrRoomObjects as $objRoom) {
            $this->arrData['arrRooms']['room_' . $objRoom->getKey('intRoomID')] = $objRoom->getSelf();
        }
        $arrSlotObjects = Object_Slot::brokerAll();
        foreach ($arrSlotObjects as $objSlot) {
            $this->arrData['arrSlots']['slot_' . $objSlot->getKey('intSlotID')] = $objSlot->getSelf();
        }
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefaultSlotTypeObjects = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefaultSlotTypeObjects as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        list($now, $next) = Object_Slot::getNowAndNext();
        foreach ($arrSlotObjects as $objSlot) {
            if ($date == null || $objSlot->getKey('startDate') == $date) {
                foreach ($arrRoomObjects as $objRoom) {
                    $objRoom->setFull(true);
                    if ($objSlot->getKey('intDefaultSlotTypeID') > 0) {
                        $this->arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')] = array(
                            'strTalkTitle' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['strDefaultSlotType'], 
                            'isLocked' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['locksSlot'],
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf(),
                            'isNow' => false,
                            'isNext' => false
                        );
                    } else {
                        $this->arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')] = array(
                            'strTalkTitle' => '', 
                            'isLocked' => 'none',
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf(),
                            'isNow' => false,
                            'isNext' => false
                        );
                    }
                    if ($objSlot->getKey('intSlotID') == $now) {
                        $this->arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')]['isNow'] = true;
                    } elseif ($objSlot->getKey('intSlotID') == $next) {
                        $this->arrData['arrTimetable']['room_' . $objRoom->getKey('intRoomID')]['slot_' . $objSlot->getKey('intSlotID')]['isNext'] = true;
                    }
                }
            }
        }
                
        if (is_array($arrTalkObjects)) {
            foreach ($arrTalkObjects as $objTalk) {
                $objTalk->setFull(true);
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    if ($date == null || $objSlot->getKey('startDate') == $date) {
                        $this->arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID] = $objTalk->getSelf();
                        if ($objTalk->getKey('isSlotLocked') == 1) {
                            $this->arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isLocked'] = 'hardlock';
                        } else {
                            $this->arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isLocked'] = 'none';
                        }
                        if ($intSlotID == $now) {
                            $this->arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isNow'] = true;
                        } elseif ($intSlotID == $next) {
                            $this->arrData['arrTimetable']['room_' . $objTalk->getKey('intRoomID')]['slot_' . $intSlotID]['isNext'] = true;
                        }
                    }
                }
            }
        }
        return $this;
    }
}

class Collection_TimetableBySlotRoom extends Collection_TimetableByRoomSlot
{
    /**
     * This function wrappers the TimetableByRoomSlot and re-jigs the timetable
     * array to match the desired layout
     *
     * @param string $date The date to return a timetable for
     * 
     * @return object
     */
    public function __construct($date = null) {
        $self = parent::__construct($date);
        foreach ($self->arrData['arrTimetable'] as $roomid => $arrslot) {
            foreach ($arrslot as $slotid => $use) {
                $tmpTimetable[$slotid][$roomid] = $use;
            }
        }
        $self->arrData['arrTimetable'] = $tmpTimetable;
        return $self;
    }
}