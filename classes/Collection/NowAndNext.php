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

class Collection_NowAndNext extends Base_GenericCollection
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
        $arrSlotObjects = Object_Slot::brokerAll();
        $arrTalkObjects = Object_Talk::brokerAll();
        $arrDefaultSlotTypeObjects = Object_DefaultSlotType::brokerAll();
        foreach ($arrDefaultSlotTypeObjects as $objDefaultSlotType) {
            $arrDefaultSlotTypes[$objDefaultSlotType->getKey('intDefaultSlotTypeID')] = $objDefaultSlotType->getSelf();
        }

        $now = null;
        $next = null;
        foreach ($arrSlotObjects as $objSlot) {
            $arrSlot = $objSlot->getSelf();
            if (date('YmdHi', strtotime($arrSlot['dateStart'] . ' ' . $arrSlot['timeStart'])) <= date('YmdHi')
                && date('YmdHi', strtotime($arrSlot['dateEnd'] . ' ' . $arrSlot['timeEnd'])) >= date('YmdHi')
                || ($now == null && date('YmdHi') <= date('YmdHi', strtotime($arrSlot['dateStart'] . ' ' . $arrSlot['timeStart'])))
                || ($now != null && $next == null)
            ) {
                if ($now == null) {
                    $now = $arrSlot['intSlotID'];
                } else {
                    $next = $arrSlot['intSlotID'];
                }
                foreach ($arrRoomObjects as $objRoom) {
                    if ($room == null || $objRoom->getKey('intRoomID') == $room) {
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