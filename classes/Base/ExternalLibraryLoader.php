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
 * This singleton class handles all the loading of external libraries.
 *
 * @category Base_ExternalLibraryLoader
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_ExternalLibraryLoader
{
    protected $_libs = array();
    protected $_externalsDir = null;

    protected static $_self = null;

    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public static function getHandler()
    {
        if (self::$_self == null) {
            self::$_self = new self();
        }
        return self::$_self;
    }

    /**
     * Construct the array of libs
     *
     * @return void
     */
    function __construct()
    {
        $this->_externalsDir = dirname(__FILE__) . '/../../ExternalLibraries';
        $result = array();
        if (file_exists("{$this->_externalsDir}/libraries.json")) {
            $this->_libs = (array) json_decode(file_get_contents("{$this->_externalsDir}/libraries.json"));
        } else {
            $arrTree = self::recurse_dir($this->_externalsDir, 0, 2);
            foreach ($arrTree as $path) {
                $newPath = substr($path, strlen($this->_externalsDir) + 1);
                $arrPath = explode('/', $newPath);
                if (count($arrPath) > 1) {
                    $result[$arrPath[0]] = $arrPath[1];
                }
            }
            $handle = fopen("{$this->_externalsDir}/libraries.json", 'w');
            if ($handle != false) {
                fwrite($handle, json_encode($result));
                fclose($handle);
            }
            $this->_libs = $result;
        }
    }

    /**
     * Find the library you're searching for, and return the highest version number.
     *
     * @param string $library The library name to search for
     *
     * @return string Library version to load
     */
    function loadLibrary($library = '')
    {
        $_self = self::getHandler();
        if (isset($_self->_libs[$library]) and file_exists($_self->_externalsDir . '/' . $library . '/' . $_self->_libs[$library])) {
            return $_self->_externalsDir . '/' . $library . '/' . $_self->_libs[$library];
        } else {
            if (file_exists($_self->_externalsDir . '/libraries.json')) {
                unlink($_self->_externalsDir . '/libraries.json');
            }
            $_self->_libs = array();
            $_self->__construct();
            if (isset($_self->_libs[$library]) and file_exists($_self->_externalsDir . '/' . $library . '/' . $_self->_libs[$library])) {
                return $_self->_externalsDir . '/' . $library . '/' . $_self->_libs[$library];
            } else {
                return false;
            }
        }
    }

    /**
     * Parse the directories and then return an array of the directories
     *
     * @param string  $dirname  Starting path
     * @param integer $level    The current depth of the search
     * @param integer $maxdepth The maximum depth to search
     *
     * @return array Directories under this starting path
     */
    protected function recurse_dir($dirname = '.', $level = 0, $maxdepth = 0)
    {
        if ($maxdepth > 0 and $level >= $maxdepth) {
            return array();
        }
        $files = array();
        $dir = opendir($dirname . '/.');
        while ($dir && ($file = readdir($dir)) !== false) {
            $path = $dirname . '/' . $file;
            if (is_dir($path) and $file != '.' and $file != '..') {
                $files[$path] = $path;
                $files = array_merge($files, $this->recurse_dir($path, $level + 1, $maxdepth));
            } else {
                // Do nothing
            }
        }
        ksort($files);
        return $files;
    }
}
