<?php
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
        $config->LoadFile('unittest.php');
        $this->assertTrue(Container_Config_Testable::brokerByID('demo', 0)->getKey('value') == 1);
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $data = $config->brokerAll();
        $item = Container_Config_Testable::brokerByID('Site_Name');
        $this->assertTrue($item->getKey('value') == 'A Demo Site');
        $item->setKey('value', 'Some Demo Data');
        Object_User::isSystem(true);
        $item->write();
        $item = Container_Config_Testable::brokerByID('Site_Name');
        $this->assertTrue($item->getKey('value') == 'Some Demo Data');
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
        $config->LoadFile('unittest.php');
        $config->set('RO_DSN', ':memory:');
        $config->LoadDatabaseConfig();
    }
    
    public function testGetUnconfiguredValue()
    {
        $this->assertTrue(Container_Config_Testable::brokerByID('UnconfiguredValue', true)->getKey('value'));
        $this->assertTrue(Container_Config_Testable::brokerByID('UnconfiguredValue')->getKey('value'));
    }
}

/**
 * In order to test the protected function, extend the class
 *
 * @category Container_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
class Container_Config_Testable extends Container_Config
{
    /**
     * Return the parent handler
     *
     * @return Container_Config
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