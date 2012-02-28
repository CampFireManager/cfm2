<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category CampFireManager2
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
 * @category Base_Config
 * @package  CampFireManager2_Base
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_Config
{
    protected static $config_handler = null;
    protected $arrConfigGlobal = null;
    protected $arrConfigSecure = null;
    protected $arrConfigLocal = null;
    protected $arrConfig = null;
    
    /**
     * This function sets up the database tables in an appropriate way for the
     * per-site and secure configuration stores.
     * 
     * @return void
     */
    function initialize()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `config` (`key` varchar(255) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            CREATE TABLE IF NOT EXISTS `secureconfig` (`key` varchar(255) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        try {
            $db = Base_Database::getConnection();
            $db->exec($sql);
        } catch (PDOException $e) {
            error_log('Initialize Base_Config failed: ' . $e->getMessage());
            die("An error occurred creating the configuration tables");
        }
    }

    /**
     * This function extends the previous function, and adds demo data to show,
     * both what sort of data would be expected in the service, and to run
     * demonstrations of the site to potential event organisers.
     * 
     * @return void
     */
    function initializeDemo()
    {
        self::initialize();
        $sql = "
            INSERT IGNORE INTO `config` (`key`, `value`) VALUES ('eventName', 'A Demo CFM Instance'), ('eventUrl', 'http://cfm2.pagekite.me'), ('hashTag', '#cfm2');
            INSERT IGNORE INTO `secureconfig` (`key`, `value`) VALUES ('salt', 'salt');
        ";
        try {
            $db = Base_Database::getConnection();
            $db->exec($sql);
        } catch (PDOException $e) {
            error_log('Initialize Base_Config failed: ' . $e->getMessage());
            die("An error occurred creating the configuration tables");
        }
    }
    
    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    private static function getHandler()
    {
        if (self::$config_handler == null) {
            self::$config_handler = new self();
        }
        return self::$config_handler;
    }

    /**
     * This singleton method is a wrapper to the getConfig function to map the
     * expected functions (as used in functional code in the rest of the site)
     * against the specific code for the Object_ orientated code used in the
     * API.
     * 
     * @param string $key Key to search for in the public (non-secure) configuration items
     * 
     * @return string expected response.
     */
    public function brokerByID($key = null)
    {
        return self::getConfig($key, null, true);
    }
    
    /**
     * This singleton method is a wrapper to the getConfig function to map the
     * expected functions (as used in functional code in the rest of the site)
     * against the specific code for the Object_ orientated code used in the
     * API.
     * 
     * @return string All public (non-secure) configuration options.
     */
    public function brokerAll()
    {
        return self::getConfig(null, null, true);
    }
    
    /**
     * This gets the value from the configuration table or file as appropriate
     *
     * @param string  $searchKey    The key to search for in the global or local config
     * @param string  $defaultValue The default to return should the key not exist.
     * @param boolean $asArray      Whether to return this data as an array or not.
     *
     * @return string The config value to use
     */
    public function getConfig($searchKey = null, $defaultValue = null, $asArray = false)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfig[$searchKey])) {
                if ($asArray == false) {
                    return $handler->arrConfig[$searchKey]['value'];
                } else {
                    return new Object_Config($searchKey, $handler->arrConfig[$searchKey]['value'], $handler->arrConfig[$searchKey]['isOverriden'], $handler->arrConfig[$searchKey]['isLocal']);
                }
            } else {
                if ($asArray == false) {
                    return $defaultValue;
                } else {
                    return array(new Object_Config($searchKey, $defaultValue, false, false));
                }
            }
        } else {
            if ($asArray == false) {
                return $handler->arrConfig;
            } else {
                $array = array();
                if (is_array($handler->arrConfig) && count($handler->arrConfig) > 0) {
                    foreach ($handler->arrConfig as $key => $config) {
                        $array[] = new Object_Config($key, $config['value'], $config['isOverriden'], $config['isLocal']);
                    }
                }
                return $array;
            }
        }
    }

    /**
    * This gets the value from the configuration file
    *
    * @param string $searchKey    The key to search for in the local config
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigLocal($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigLocal[$searchKey])) {
                return $handler->arrConfigLocal[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigLocal;
        }
    }

    /**
    * This gets the value from the configuration table
    *
    * @param string $searchKey    The key to search for in the global config
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigGlobal($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigGlobal[$searchKey])) {
                return $handler->arrConfigGlobal[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigGlobal;
        }
    }

    /**
    * This gets the value from the configuration table
    *
    * @param string $searchKey    The key to search for in the secure config.
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigSecure($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigSecure[$searchKey])) {
                return $handler->arrConfigSecure[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigSecure;
        }
    }

    /**
     * This function reads the config sources to use later
     *
     * @return void
     */
    private function readConfig()
    {
        $handler = self::getHandler();
        if (null == $handler->arrConfigGlobal && null == $handler->arrConfigLocal && null == $handler->arrConfigSecure) {
            include dirname(__FILE__) . '/../../config/default.php';

            $handler->arrConfigLocal = $APPCONFIG;

            $db = Base_Database::getConnection();
            try {
                // This is just for things like API keys, password salts etc.
                // If we have, at a later point, a function to export the database
                // en masse, then we can encrypt all the output from the secureconfig
                // table. It shouldn't overide the global or local config settings
                $sql = "SELECT * FROM secureconfig";
                $query = $db->prepare($sql);
                $query->execute();
                $handler->arrConfigSecure = $query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                // This is just the regular global configuration settings.
                $sql = "SELECT * FROM config";
                $query = $db->prepare($sql);
                $query->execute();
                $handler->arrConfigGlobal = $query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
                foreach ($handler->arrConfigGlobal as $key => $value) {
                    $handler->arrConfig[$key] = array('isLocal' => false, 'isOverriden' => false, 'value' => $value);
                }
                
                // This is the configuration settings local to this individual machine.
                foreach ($handler->arrConfigLocal as $key => $value) {
                    if (isset($handler->arrConfig[$key])) {
                        $handler->arrConfig[$key] = array('isLocal' => true, 'isOverriden' => true, 'value' => $value);
                    } else {
                        $handler->arrConfig[$key] = array('isLocal' => true, 'isOverriden' => false, 'value' => $value);
                    }
                }
            } catch(Exception $e) {
                error_log($e);
                die();
            }
        }
    }

    /**
     * This function lets us update (add, update, delete) the config table in the database.
     *
     * @param string $key   The key to use to update the config table
     * @param string $value The value to store in the config table. Set to null to erase the entry.
     *
     * @return boolean Success or failure of the operation.
     */
    function setGlobalConfig($key = null, $value = null)
    {
        if (null == $key) {
            return false;
        }
        $db = Base_Database::getConnection();
        try {
            $sql = "SELECT value FROM config WHERE key = ? LIMIT 0, 1";
            $query = $db->prepare($sql);
            $query->execute(array($key));
            $finder = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($finder == false) {
                $sql = "INSERT INTO config (key, value) VALUES (?, ?)";
                $query = $db->prepare($sql);
                $query->execute(array($key, $value));
            } else {
                if (null == $value) {
                    $sql = "DELETE FROM config WHERE key = ?";
                    $query = $db->prepare($sql);
                    $query->execute(array($key));
                } else {
                    $sql = "UPDATE config SET value = ? WHERE key = ?";
                    $query = $db->prepare($sql);
                    $query->execute(array($value, $key));
                }
            }
        } catch(Exception $e) {
            error_log($e);
            return false;
        }
        return true;
    }
}

/**
 * This class is a wrapper for the Configuration objects retrieved for use
 * in the API. It is functionally sound, as it will allow the user to set global
 * configuration options. These, however, will be overriden by the local config
 * files.
 *
 * @category Base
 * @package  Config
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Config extends Base_GenericObject
{
    protected $arrDBItems = array('value' => null);
    protected $strDBTable = "config";
    protected $strDBKeyCol = "key";
    protected $key = null;
    protected $value = null;
    protected $isOverriden = null;
    protected $isLocal = null;

    /**
     * This function creates an object containing the Configuration Values provided to it
     * 
     * @param string  $key         The name of the key to use
     * @param string  $value       The value of that key
     * @param boolean $isOverriden If it does not match the value in the SQL server, as a local configuration file has changed it.
     * @param boolean $isLocal     If it is based on a value present only in the local system
     * 
     * @return object The constructed "Object" for use elsewhere.
     */
    function __construct($key = null, $value = null, $isOverriden = null, $isLocal = null)
    {
        $this->key = $key;
        $this->value = $value;
        return $this;
    }
}