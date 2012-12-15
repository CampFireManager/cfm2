<?php
class Object_InputTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Base_Cache::flush();
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('unittest.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objInput = new Object_Input();
        $objInput->initializeDemo();
    }
    
    public function testObjectInputCreation()
    {
        $objConfig = new Object_Input();
        $this->assertTrue(is_object($objConfig));
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testObjectInputCreationNotAsSystem()
    {
        Object_User::isSystem(false);
        $objConfig = new Object_Input();
        $this->assertTrue(is_object($objConfig));
    }
    
    public function testReceivedSMS()
    {
        $message = Object_Input::import("+447000000001", "Gammu-private_Glue-Gammu-Provider", "Hello", "1");
        $this->assertTrue(is_object($message));
        $this->assertTrue($message->getKey('strSender') == '+447000000001');
        $this->assertTrue($message->getKey('strInterface') == "Gammu-private_Glue-Gammu-Provider");
        $this->assertTrue($message->getKey('textMessage') == "Hello");
        $this->assertTrue($message->getKey('intNativeID') == "1");
    }

}