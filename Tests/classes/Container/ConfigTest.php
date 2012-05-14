<?php
require_once dirname(__FILE__) . '/../../../classes/autoloader.php';

class Container_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Container_Config_Testable::reset();
    }
    
    public function testInitialize()
    {
        $config = Container_Config_Testable::GetHandler();
        $this->assertTrue(is_object($config));
        $this->assertTrue(get_class($config) == 'Container_Config');
    }
    
    public function testInitializeConfigLoader()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('democonfig.php');
        $this->assertTrue($config->get('demo') == 1);
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testNoFilename()
    {
        $config = Container_Config_Testable::GetHandler();
        @$config->LoadFile();
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidFilename()
    {
        $config = Container_Config_Testable::GetHandler();
        @$config->LoadFile('A Dummy File');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnloadableConfig()
    {
        $config = Container_Config_Testable::GetHandler();
        @$config->LoadFile('.');
    }

    public function testReloadingValues()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->set('demo', 'true');
        $config->set('demo', 'false');
    }
    
    public function testSettingDsn()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('democonfig.php');
        $config->set('RO_DSN', ':memory:');
        $config->LoadDatabaseConfig();
    }
    
    public function testGetUnconfiguredValue()
    {
        $config = Container_Config_Testable::GetHandler();
        $this->assertTrue($config->get('UnconfiguredValue', true));
        $this->assertTrue($config->get('UnconfiguredValue') == null);
    }
}

/**
 * In order to test the protected function, extend the class
 *
 * @category Container_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Container_Config_Testable extends Container_Config
{
    /**
     * Return the parent handler
     *
     * @return object
     */
    public static function GetHandler()
    {
        return parent::GetHandler();
    }
    
    /**
     * Reset the class for testing purposes
     * 
     * @return void
     */
    public static function reset()
    {
        parent::reset();
    }
}