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

class Collection_MainScreen extends Collection_Timetable
{
    /**
     * A mock up of the Object_ style of broker functions
     *
     * @param string $date The date of the timetable to retrieve. Leave blank for all dates known
     * 
     * @return array
     */
    public function __construct($date = null)
    {
        parent::__construct($date);
        foreach ($this->arrData['arrTimetable'] as $x_id => $y_axis) {
            foreach ($y_axis as $y_id => $data) {
                $tmpTimetable[$y_id][$x_id] = $data;
            }
        }
        $this->arrData['arrTimetable'] = $tmpTimetable;
    }
}
