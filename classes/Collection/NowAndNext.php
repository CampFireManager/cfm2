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
 * This class collates all the objects needed to render a NowAndNext Page
 *
 * @category Collection_NowAndNext
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Collection_NowAndNext extends Collection_Timetable
{
    /**
     * Collect the data for this collection
     *
     * @param integer|null $room       The room to return, or null for all of them
     * @param string|null  $strNowTime The time string to base the now & next data on
     * 
     * @return Collection_NowAndNext
     */
    protected function __construct($room = null, $strNowTime = null)
    {        
        parent::__construct($strNowTime);
        list($intNow, $intNext) = Object_Slot::getNowAndNext($strNowTime);
        // Check that x_axis = room and y_axis = slot, then isNow or isNext == 1
        // lastly check $room == intRoomID
        if ($this->arrData['x_axis'] == 'room') {
            foreach ($this->arrData['arrTimetable'] as $intRoomID => $y_axis) {
                if ($room == null || $intRoomID == 'room_' . $room) {
                    foreach ($y_axis as $intSlotID => $slot) {
                        if ($intSlotID == 'slot_' . $intNow || $intSlotID = 'slot_' . $intNext) {
                            $tmpTimetable[$intRoomID][$intSlotID]  = $slot;
                        }
                    }
                }
            }
        } elseif ($this->arrData['x_axis'] == 'slot') {
            foreach ($this->arrData['arrTimetable'] as $intSlotID => $y_axis) {
                if ($intSlotID == 'slot_' . $intNow || $intSlotID == 'slot_' . $intNext) {
                    foreach ($y_axis as $intRoomID => $slot) {
                        if ($room == null || $intRoomID == 'room_' . $room) {
                            $tmpTimetable[$intRoomID][$intSlotID]  = $slot;
                        }
                    }
                }
            }
        }
        $this->arrData['arrTimetable'] = $tmpTimetable;
        unset($this->arrData['x_axis']);
        unset($this->arrData['y_axis']);
    }
    
    /**
     * A mock up of the Object_ style of broker functions, for collections of data (not quite working the same!)
     *
     * @param integer|null $room The intRoomID for the room to show the now&next screen for, or null for all
     * 
     * @return array
     */
    public static function brokerByID($room = null)
    {
        return parent::brokerByID($room);
    }
    
    
}