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
     * Collect the data for this collection
     *
     * @param integer|null $date The date to return, or everything if null
     * 
     * @return object This class 
     */
    protected function __construct($date = null)
    {
        $arrRoomObjects = Object_Room::brokerAll();
        $arrSlotObjects = Object_Slot::brokerAll();
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefaultSlotTypeObjects = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefaultSlotTypeObjects as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        foreach ($arrSlotObjects as $objSlot) {
            if ($date == null || $objSlot->getKey('startDate') == $date) {
                foreach ($arrRoomObjects as $objRoom) {
                    $objRoom->setFull(true);
                    if ($objSlot->getKey('intDefaultSlotTypeID') > 0) {
                        $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')] = array(
                            'strTalkTitle' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['strDefaultSlotType'], 
                            'isLocked' => $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]['locksSlot'],
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf()
                        );
                    } else {
                        $this->arrData[$objRoom->getKey('intRoomID')][$objSlot->getKey('intSlotID')] = array(
                            'strTalkTitle' => '', 
                            'isLocked' => 'none',
                            'arrRoom' => $objRoom->getSelf(),
                            'arrSlot' => $objSlot->getSelf()
                        );
                    }
                }
            }
        }
                
        if (is_array($arrTalkObjects)) {
            foreach ($arrTalkObjects as $objTalk) {
                $objTalk->setFull(true);
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    if ($date == null || $objSlot->getKey('startDate') == $date) {
                        $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID] = $objTalk->getSelf();
                        if ($objTalk->getKey('isSlotLocked') == 1) {
                            $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID]['isLocked'] = 'hardlock';
                        } else {
                            $this->arrData[$objTalk->getKey('intRoomID')][$intSlotID]['isLocked'] = 'none';
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