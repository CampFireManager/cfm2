<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category CampFireManager2
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class collates all the objects needed to render a full timetable
 *
 * @category Collection_Timetable
 * @package  CampFireManager2_Collections
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Collection_Timetable
{
    protected $arrData = array();
    
    /**
     * A mock up of the Object_ style of broker functions, for collections of data (not quite working the same!)
     *
     * @return array
     */
    public static function brokerAll()
    {
        return Collection_Timetable::brokerByID();
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
        
        $this->arrData['Rooms'] = Object_Room::brokerAll();
        $this->arrData['Slots'] = Object_Slot::brokerAll();
        $this->arrData['Talks'] = Object_Talk::brokerAll();

        foreach ($this->arrData['Rooms'] as $room) {
            $room->setFull(true);
            $this->arrData['Timetable']['Rooms'][$room->getKey('intRoomID')] = $room->getSelf();
        }
        foreach ($this->arrData['Slots'] as $slot) {
            $slot->setFull(true);
            $this->arrData['Timetable']['Slots'][$slot->getKey('intSlotID')] = $slot->getSelf();
        }
        
        if (is_array($this->arrData['Rooms']) && is_array($this->arrData['Slots'])) {
            foreach ($this->arrData['Rooms'] as $room) {
                foreach ($this->arrData['Slots'] as $slot) {
                    if ($date == null || $slot->getKey('startDate') == $date) {
                        $this->arrData['Timetable']['Room_' . $room->getKey('intRoomID')]['Slot_' . $slot->getKey('intSlotID')] = $slot->getKey('intDefaultSlotTypeID');
                    }
                }
            }
        }
        
        if (is_array($this->arrData['Talks'])) {
            foreach ($this->arrData['Talks'] as $talk) {

                $talk->setFull(true);
                for ($slot = $talk->getKey('intSlotID'); $slot < $talk->getKey('intSlotID') + $talk->getKey('intLength'); $slot++) {
                    if ($date == null || $slot->getKey('startDate') == $date) {
                        $this->arrData['Timetable']['Room_' . $talk->getKey('intRoomID')]['Slot_' . $slot] = $talk->getSelf();
                    }
                }
            }
        }
        return $this->arrData;
    }

    /**
     * A function to return all the timetable data. This will probably be superceeded by something.
     *
     * @return array
     */
    
    public function getData()
    {
        return $this->arrData['Timetable'];
    }
}