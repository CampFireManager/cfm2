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
 * This class returns an array of Glues.
 *
 * @category Glue_Broker
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Glue_Broker
{
    /**
     * Return an array of glues.
     *
     * @return array 
     */
    public static function brokerAll() {
        $arrGlues = array();
        foreach (new DirectoryIterator(dirname(__FILE__)) as $file) {
            if ($file->isDir() || $file->isDot()) continue;
            if ($file->isFile() 
                && ($file->getBasename('.php') != $file->getBasename()) 
                && $file->getBasename() != 'Broker.php'
            ) {
                $strGlueName = 'Glue_' . $file->getBasename('.php');
                foreach ($strGlueName::brokerAllGlues() as $objGlue) {
                    $arrGlues[] = $objGlue;
                }
            }
        }
        return $arrGlues;
    }    
}
