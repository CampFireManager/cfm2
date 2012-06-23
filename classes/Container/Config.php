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
    protected static $self      = null;
    protected $arrConfig        = array();
    protected $arrSecureConfig  = array();
    protected $isFileLoaded     = false;
    protected $isDatabaseLoaded = false;
    protected $fileModifiedTime = null;
    // Database Columns
    protected $key = null;
    protected $value = null;
    protected $lastChange = null;
    
    /**
     * This protected function helps make this class a singleton
     *
     * @return object
     */
    protected static function GetHandler()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }
    
    /**
     * This protected function lets us reset the class for Unit Testing
     * 
     * @return void
     */
    protected static function reset()
    {
        self::$self = null;
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
    ) {
        $self = self::GetHandler();
        if (! $self->isFileLoaded || $doReloadFile == true) {
            try {
                if ($strFileName == null) {
                    $strFileName = 'default.php';
                }
                $self->LoadFile($strFileName);
                $self->isFileLoaded = true;
            } catch (Exception $e) {
                throw $e;
            }
        }

        if (! $self->isDatabaseLoaded 
            || $doReloadDatabase == true 
            || $doReloadFile == true
        ) {
            try {
                $self->SetUpDatabaseConnection();
                $self->LoadDatabaseConfig();
                $self->isDatabaseLoaded = true;
            } catch (Exception $e) {
                throw $e;
            }            
        }
        return $self;
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
        
        $this->fileModifiedTime = filemtime(
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
            'string' => $this->arrConfig['DatabaseType']->getKey('value') . ':' 
                      . $this->arrConfig['RW_DSN']->getKey('value'),
            'user' => $this->arrConfig['RW_User']->getKey('value'),
            'pass' => $this->arrConfig['RW_Pass']->getKey('value'),
            'init' => array()
        );
        if (isset($this->arrConfig['DatabaseInit'])) {
            $connectionReadWrite['init']
                = $this->arrConfig['DatabaseInit']->getKey('value');
        }
        if (isset($this->arrConfig['RO_DSN']) 
            && $this->arrConfig['RO_DSN']->getKey('value') != null 
            && isset($this->arrConfig['RO_User']) 
            && isset($this->arrConfig['RO_Pass'])
        ) {
            $connectionReadOnly = array(
                'string' => $this->arrConfig['DatabaseType']->getKey('value') 
                    . ':' . $this->arrConfig['RO_DSN']->getKey('value'),
                'user' => $this->arrConfig['RO_User']->getKey('value'),
                'pass' => $this->arrConfig['RO_Pass']->getKey('value'),
                'init' => array()
            );
            if (isset($this->arrConfig['DatabaseInit'])) {
                $connectionReadOnly['init'] 
                    = $this->arrConfig['DatabaseInit']->getKey('value');
            }
        } else {
            $connectionReadOnly = null;
        }
        Container_Database::setConnection(
            $this->arrConfig['DatabaseType']->getKey('value'), 
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
                $this->arrConfig[$value->getKey('key')] = $value;
            }
        }

        $allSecureConfig = Object_SecureConfig::brokerAll();
        if (is_array($allSecureConfig) && count($allSecureConfig) > 0) {
            foreach ($allSecureConfig as $value) {
                $this->arrSecureConfig[$value->getKey('key')] = $value;
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
        if (! isset($this->arrConfig[$key])) {
            $this->arrConfig[$key] = new Object_Config(
                array(
                    'key' => $key, 
                    'value' => $value
                ),
                date('Y-m-d H:i:s', $this->fileModifiedTime)
            );
        } else {
            $this->arrConfig[$key]->setKey('value', $value);
            $this->arrConfig[$key]->setKey(
                'lastChange', 
                date('Y-m-d H:i:s', $this->fileModifiedTime)
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
        $self = self::GetHandler();
        if (! isset($self->arrConfig[$key])) {
            return new Object_Config(
                array(
                    'key' => $key,
                    'value' => $mixedDefaultValue
                ),
                date('Y-m-d H:i:s')
            );
        } else {
            return $self->arrConfig[$key];
        }
    }
    
    public static function brokerAll()
    {
        $self = self::GetHandler();
        if (isset($self->arrConfig)
            && is_array($self->arrConfig)
            && count($self->arrConfig) > 0
        ) {
            return $self->arrConfig;
        }
        return array();
    }

    public static function brokerByColumnSearch($column = null, $value = null)
    {
        $self = self::GetHandler();
        if ($column == null) {
            return false;
        } elseif (!isset($self->arrConfig) 
            || !is_array($self->arrConfig)
            || count($self->arrConfig) == 0
        ) {
            return false;
        } elseif ($column != 'key' && $column != 'value') {
            return false;
        }
        if ($column == 'key') {
            if (isset($self->arrConfig[$key])) {
                return $self->arrConfig[$key];
            }
            foreach ($self->arrConfig as $key => $object) {
                if (strstr($object->getKey('key'), $value)) {
                    return $object;
                }
            }
        } else {
            foreach ($self->arrConfig as $key => $object) {
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
        $self = self::GetHandler();
        if ($column == null) {
            return 0;
        } elseif (!isset($self->arrConfig) 
            || !is_array($self->arrConfig)
            || count($self->arrConfig) == 0
        ) {
            return 0;
        } elseif ($column != 'key' && $column != 'value') {
            return 0;
        }
        $counter = 0;
        foreach ($self->arrConfig as $object) {
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
        $self = self::GetHandler();
        if ($column == null) {
            return false;
        } elseif (!isset($self->arrConfig) 
            || !is_array($self->arrConfig)
            || count($self->arrConfig) == 0
        ) {
            return false;
        } elseif ($column != 'key' && $column != 'value') {
            return false;
        }
        if ($column == 'key') {
            if (isset($self->arrConfig[$key])) {
                return $self->arrConfig[$key]->getKey('lastChange');
            }
            foreach ($self->arrConfig as $key => $object) {
                if (strstr($object->getKey('key'), $value)) {
                    return $object->getKey('lastChange');
                }
            }
        } else {
            foreach ($self->arrConfig as $key => $object) {
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
        $self = self::GetHandler();
        if (!isset($self->arrConfig) 
            || !is_array($self->arrConfig)
            || count($self->arrConfig) == 0
        ) {
            return false;
        }
        $lastChange = 0;
        foreach ($self->arrConfig as $object) {
            if (strtotime($object->getKey('lastChange')) > $lastChange) {
                $lastChange = strtotime($object->getKey('lastChange'));
            }
        }
        return $lastChange;
    }
    
    public static function countAll()
    {
        $self = self::GetHandler();
        if (!isset($self->arrConfig) 
            || !is_array($self->arrConfig)
        ) {
            return 0;
        } else {
            return count($self->arrConfig);
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
        $self = self::GetHandler();
        if (! isset($self->arrSecureConfig[$key])) {
            return new Object_SecureConfig(
                array(
                    'key' => $key,
                    'value' => $mixedDefaultValue
                ),
                date('Y-m-d H:i:s')
            );
        } else {
            return $self->arrSecureConfig[$key];
        }
    }
}