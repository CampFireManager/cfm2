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
        $config = Container_Config::GetLoadedConfig('democonfig.php');
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