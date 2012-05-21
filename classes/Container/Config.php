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
 * This class obtains manipulates all the configuration data for the service. It
 * handles local configuration (per-server), global configuration (per-site) and
 * secure configuration (api keys, password salts etc.)
 *
 * @category Container_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Container_Config implements Interface_Object
{
    protected static $_self      = null;
    protected $_arrConfig        = array();
    protected $_arrSecureConfig  = array();
    protected $_isFileLoaded     = false;
    protected $_isDatabaseLoaded = false;
    protected $_fileModifiedTime = null;
    
    /**
     * This protected function helps make this class a singleton
     *
     * @return object
     */
    protected static function GetHandler()
    {
        if (self::$_self == null) {
            self::$_self = new self();
        }
        return self::$_self;
    }
    
    /**
     * This protected function lets us reset the class for Unit Testing
     * 
     * @return void
     */
    protected static function reset()
    {
        self::$_self = null;
    }
    
    /**
     * This function loads or reloads the config file and the config and
     * secureconfig database tables into the system
     *
     * @param string  $strFileName      The file to load
     * @param boolean $doReloadDatabase A switch to force reloading the database
     * tables where the database has been loaded previously.
     * @param boolean $doReloadFile     A switch to force reloading the config
     * file, which in turn will force a reload of the database.
     * 
     * @return object 
     */
    public static function LoadConfig(
        $strFileName = null, 
        $doReloadDatabase = false, 
        $doReloadFile = false
    )
    {
        $_self = self::GetHandler();
        if (! $_self->_isFileLoaded || $doReloadFile == true) {
            try {
                if ($strFileName == null) {
                    $strFileName = 'default.php';
                }
                $_self->LoadFile($strFileName);
                $_self->_isFileLoaded = true;
            } catch (Exception $e) {
                throw $e;
            }
        }

        if (! $_self->_isDatabaseLoaded 
            || $doReloadDatabase == true 
            || $doReloadFile == true
        ) {
            try {
                $_self->SetUpDatabaseConnection();
                $_self->LoadDatabaseConfig();
                $_self->_isDatabaseLoaded = true;
            } catch (Exception $e) {
                throw $e;
            }            
        }
        return $_self;
    }

    /**
     * This function reads the contents of a config file into the class
     *
     * @param string $strFileName The file to read.
     * 
     * @return void
     */
    public function LoadFile($strFileName = null)
    {
        if ($strFileName == null) {
            throw new UnexpectedValueException(
                "You did not specify a filename."
            );
        }
        
        if (!file_exists(dirname(__FILE__) . '/../../config/' . $strFileName)) {
            throw new UnexpectedValueException("This file does not exist.");
        }
        
        $this->_fileModifiedTime = filemtime(
            realpath(dirname(__FILE__) . '/../../config/' . $strFileName)
        );
        
        if (! include dirname(__FILE__) . '/../../config/' . $strFileName) {
            throw new InvalidArgumentException("Can't load this file");
        }
    }

    /**
     * This function establishes the database connection strings
     * 
     * @return void
     */
    public function SetUpDatabaseConnection()
    {
        $connectionReadWrite = array(
            'string' => $this->_arrConfig['DatabaseType']->getKey('value') . ':' 
                      . $this->_arrConfig['RW_DSN']->getKey('value'),
            'user' => $this->_arrConfig['RW_User']->getKey('value'),
            'pass' => $this->_arrConfig['RW_Pass']->getKey('value'),
            'init' => array()
        );
        if (isset($this->_arrConfig['DatabaseInit'])) {
            $connectionReadWrite['init']
                = $this->_arrConfig['DatabaseInit']->getKey('value');
        }
        if (isset($this->_arrConfig['RO_DSN']) 
            && $this->_arrConfig['RO_DSN']->getKey('value') != null 
            && isset($this->_arrConfig['RO_User']) 
            && isset($this->_arrConfig['RO_Pass'])
        ) {
            $connectionReadOnly = array(
                'string' => $this->_arrConfig['DatabaseType']->getKey('value') 
                    . ':' . $this->_arrConfig['RO_DSN']->getKey('value'),
                'user' => $this->_arrConfig['RO_User']->getKey('value'),
                'pass' => $this->_arrConfig['RO_Pass']->getKey('value'),
                'init' => array()
            );
            if (isset($this->_arrConfig['DatabaseInit'])) {
                $connectionReadOnly['init'] 
                    = $this->_arrConfig['DatabaseInit']->getKey('value');
            }
        } else {
            $connectionReadOnly = null;
        }
        Container_Database::setConnection(
            $this->_arrConfig['DatabaseType']->getKey('value'), 
            $connectionReadOnly, 
            $connectionReadWrite
        );
    }
    
    /**
     * Once the database is connected, read the Config and SecureConfig into
     * the container.
     * 
     * @return void
     */
    public function LoadDatabaseConfig()
    {
        $allConfig = Object_Config::brokerAll();
        if (is_array($allConfig) && count($allConfig) > 0) {
            foreach ($allConfig as $value) {
                $this->_arrConfig[$value->getKey('key')] = $value;
            }
        }

        $allSecureConfig = Object_SecureConfig::brokerAll();
        if (is_array($allSecureConfig) && count($allSecureConfig) > 0) {
            foreach ($allSecureConfig as $value) {
                $this->_arrSecureConfig[$value->getKey('key')] = $value;
            }
        }
    }

    /**
     * This function creates new Config objects for the received data
     *
     * @param string $key   Key to store
     * @param mixed  $value Value to keep or replace in the object
     * 
     * @return void
     */
    public function set($key = null, $value = null)
    {
        if (! isset($this->_arrConfig[$key])) {
            $this->_arrConfig[$key] = new Object_Config(
                array(
                    'key' => $key, 
                    'value' => $value
                ),
                date('Y-m-d H:i:s', $this->_fileModifiedTime)
            );
        } else {
            $this->_arrConfig[$key]->setKey('value', $value);
            $this->_arrConfig[$key]->setKey(
                'lastChange', 
                date('Y-m-d H:i:s', $this->_fileModifiedTime)
            );
        }
    }
    
    /**
     * Pull the config value out of the config array, or the default value if
     * it's not already set.
     *
     * @param string $key               The array key to look for
     * @param mixed  $mixedDefaultValue The value to return if the key doesn't
     * exist
     *
     * @return string
     */
    public static function brokerByID($key = null, $mixedDefaultValue = null)
    {
        $_self = self::GetHandler();
        if (! isset($_self->_arrConfig[$key])) {
            return new Object_Config(
                array(
                    'key' => $key,
                    'value' => $mixedDefaultValue
                ),
                date('Y-m-d H:i:s')
            );
        } else {
            return $_self->_arrConfig[$key];
        }
    }
    
    public static function brokerAll()
    {
        $_self = self::GetHandler();
        if (isset($_self->_arrConfig)
            && is_array($_self->_arrConfig)
            && count($_self->_arrConfig) > 0
        ) {
            return $_self->_arrConfig;
        }
        return array();
    }

    public static function brokerByColumnSearch($column = null, $value = null)
    {
        $_self = self::GetHandler();
        if ($column == null) {
            return false;
        } elseif (!isset($_self->_arrConfig) 
            || !is_array($_self->_arrConfig)
            || count($_self->_arrConfig) == 0
        ) {
            return false;
        } elseif ($column != 'key' && $column != 'value') {
            return false;
        }
        if ($column == 'key') {
            if (isset($_self->_arrConfig[$key])) {
                return $_self->_arrConfig[$key];
            }
            foreach ($_self->_arrConfig as $key => $object) {
                if (strstr($object->getKey('key'), $value)) {
                    return $object;
                }
            }
        } else {
            foreach ($_self->_arrConfig as $key => $object) {
                if ($object->getKey('value') == $value) {
                    return $object;
                } elseif (strstr($object->getKey('value'), $value)) {
                    return $object;
                }
            }
        }
        return false;
    }
    
    public static function countByColumnSearch($column = null, $value = null)
    {
        $_self = self::GetHandler();
        if ($column == null) {
            return 0;
        } elseif (!isset($_self->_arrConfig) 
            || !is_array($_self->_arrConfig)
            || count($_self->_arrConfig) == 0
        ) {
            return 0;
        } elseif ($column != 'key' && $column != 'value') {
            return 0;
        }
        $counter = 0;
        foreach ($_self->_arrConfig as $object) {
            if ($object->getKey($column) == $value 
                || strstr($object->getKey($column), $value)
            ) {
                $counter++;
            }
        }
        return $counter;
    }
    
    public static function lastChangeByColumnSearch($column = null, $value = null)
    {
        $_self = self::GetHandler();
        if ($column == null) {
            return false;
        } elseif (!isset($_self->_arrConfig) 
            || !is_array($_self->_arrConfig)
            || count($_self->_arrConfig) == 0
        ) {
            return false;
        } elseif ($column != 'key' && $column != 'value') {
            return false;
        }
        if ($column == 'key') {
            if (isset($_self->_arrConfig[$key])) {
                return $_self->_arrConfig[$key]->getKey('lastChange');
            }
            foreach ($_self->_arrConfig as $key => $object) {
                if (strstr($object->getKey('key'), $value)) {
                    return $object->getKey('lastChange');
                }
            }
        } else {
            foreach ($_self->_arrConfig as $key => $object) {
                if ($object->getKey('value') == $value) {
                    return $object->getKey('lastChange');
                } elseif (strstr($object->getKey('value'), $value)) {
                    return $object->getKey('lastChange');
                }
            }
        }
        return false;
    }
    
    public static function lastChangeAll()
    {
        $_self = self::GetHandler();
        if (!isset($_self->_arrConfig) 
            || !is_array($_self->_arrConfig)
            || count($_self->_arrConfig) == 0
        ) {
            return false;
        }
        $lastChange = 0;
        foreach ($_self->_arrConfig as $object) {
            if (strtotime($object->getKey('lastChange')) > $lastChange) {
                $lastChange = strtotime($object->getKey('lastChange'));
            }
        }
        return $lastChange;
    }
    
    public static function countAll()
    {
        $_self = self::GetHandler();
        if (!isset($_self->_arrConfig) 
            || !is_array($_self->_arrConfig)
        ) {
            return 0;
        } else {
            return count($_self->_arrConfig);
        }
    }

    /**
     * Pull the config value out of the config array, or the default value if
     * it's not already set.
     *
     * @param string $key               The array key to look for
     * @param mixed  $mixedDefaultValue The value to return if the key doesn't
     * exist
     *
     * @return string
     */
    public function getSecureByID($key = null, $mixedDefaultValue = null)
    {
        $_self = self::GetHandler();
        if (! isset($_self->_arrSecureConfig[$key])) {
            return new Object_SecureConfig(
                array(
                    'key' => $key,
                    'value' => $mixedDefaultValue
                ),
                date('Y-m-d H:i:s')
            );
        } else {
            return $_self->_arrSecureConfig[$key];
        }
    }
    
}