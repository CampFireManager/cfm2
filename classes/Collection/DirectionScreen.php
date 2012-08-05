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

class Collection_DirectionScreen extends Abstract_GenericCollection
{
    /**
     * Collect the data for this collection
     *
     * @param integer|null $screen The screen ID to return, or null to create a
     * new screen and reload to that page.
     * 
     * @return Collection_DirectionScreen
     */
    protected function __construct($screen = null)
    {
        if ($screen != null) {
            $objScreen = Object_Screen::brokerByID($screen);
        }
        if ($screen == null || $objScreen == false) {
            $objScreen = new Object_Screen(true);
            $objScreen->setKey('dtLastSeen', date('Y-m-d H:i:s'));
            $objScreen->create();
            Base_Response::redirectTo('directionscreen/' . $objScreen->getPrimaryKeyValue());
        }
        $objScreen->setKey('dtLastSeen', date('Y-m-d H:i:s'));
        $objScreen->write();

        $arrScreen = $objScreen->getSelf();

        $arrRooms = Object_Room::brokerAll();
        foreach ($arrRooms as $objRoom) {
            $arrTalks['now'][$objRoom->getKey('intRoomID')] = null;
            $arrTalks['next'][$objRoom->getKey('intRoomID')] = null;
        }

        list($now, $next) = Object_Slot::getNowAndNext();
        $nowSlot = Object_Slot::brokerByID($now);
        if (is_object($nowSlot)) {
            $nowSlot->setFull(true);
            $nowSlot = $nowSlot->getSelf();
        }
        $nextSlot = Object_Slot::brokerByID($next);
        if (is_object($nextSlot)) {
            $nextSlot->setFull(true);
            $nextSlot = $nextSlot->getSelf();
        }

        $this->arrData = array('NowSlot' => $nowSlot, 'NextSlot' => $nextSlot);
        
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
        $arrRoomDirections = array();
        
        $arrNTalks = Object_Talk::brokerByColumnSearch('intSlotID', $now);
        if ($arrNTalks == false) {
            $arrNTalks = array();
        }
        foreach ($arrNTalks as $objTalk) {
            $objTalk->setFull(true);
            $arrTalks['now'][$objTalk->getKey('intRoomID')] = $objTalk->getSelf();
        }

        $arrNTalks = Object_Talk::brokerByColumnSearch('intSlotID', $next);
        if ($arrNTalks == false) {
            $arrNTalks = array();
        }
        foreach ($arrNTalks as $objTalk) {
            $objTalk->setFull(true);
            $arrTalks['next'][$objTalk->getKey('intRoomID')] = $objTalk->getSelf();
        }
        
        foreach ($arrScreen['arrDirections'] as $strDirection => $arrDirections) {
            ksort($arrDirections);
            foreach ($arrDirections as $intRoomID => $objDirection) {
                $this->arrData[$strDirection][$objDirection->getKey('intScreenDirectionID')] = array(
                    'strRoom' => $arrRooms[$intRoomID]->getKey('strRoom'),
                    'now' => $arrTalks['now'][$intRoomID],
                    'next' => $arrTalks['next'][$intRoomID]
                );
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