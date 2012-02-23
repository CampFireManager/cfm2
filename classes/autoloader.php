<?php
/**
 * CCHits.net is a website designed to promote Creative Commons Music,
 * the artists who produce it and anyone or anywhere that plays it.
 * These files are used to generate the site.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CCHitsClass
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     http://cchits.net Actual web service
 * @link     http://code.cchits.net Developers Web Site
 * @link     http://gitorious.net/cchits-net Version Control Service
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
        $class_path .= '/' . $class_point;
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
