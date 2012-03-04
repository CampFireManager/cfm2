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

class Collection_TimetablePortrait extends Collection_Timetable
{
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