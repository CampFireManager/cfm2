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
 * A basic autoloader
 *
 * @param string $className The name of the class we're trying to load
 *
 * @return true|false Whether we were able to load the class.
 */
function __autoload($className)
{
    $arrClass = explode('_', $className);
    $class_path  = dirname(__FILE__);
    foreach ($arrClass as $class_point) {
        if ($class_point != 'Demo') {
            $class_path .= '/' . $class_point;
        }
    }
    if ($arrClass[0] == 'Plugin') {
        $class_path .= '/hook_loader';
    }
    if (is_file($class_path . '.php')) {
        include_once $class_path . '.php';
        return true;
    }
    return false;
}
