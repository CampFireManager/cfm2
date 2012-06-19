<?php
class Object_UserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('democonfig.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objUser = new Object_User_Demo();
        $objUser->initializeDemo();
        Base_Cache::flush();
    }

    public function testObjectUserCreation()
    {
        $objUser = new Object_User();
        $this->assertTrue(is_object($objUser));
    }
        
    public function testSystemUser()
    {
        $this->assertFalse(Object_User::isSystem());
        $this->assertTrue(Object_User::isSystem(true));
        $this->assertTrue(Object_User::isSystem());
        $this->assertFalse(Object_User::isSystem(false));
        $this->assertFalse(Object_User::isSystem());
    }

    public function testIsAdmin()
    {
        $this->assertFalse(Object_User::isAdmin());
        $this->assertFalse(Object_User::isCreator());
        Object_User::isSystem(true);
        $this->assertTrue(Object_User::isAdmin());
        $this->assertTrue(Object_User::isCreator());
        Object_User::isSystem(false);
        $this->assertFalse(Object_User::isAdmin(3)); // User 3 is a worker
        $this->assertTrue(Object_User::isAdmin(2)); // User 2 is an admin
        $this->assertFalse(Object_User::isAdmin(1)); // User 1 is a regular person
        $this->assertTrue(Object_User::isCreator(1, 1));
        $this->assertFalse(Object_User::isCreator(2, 1)); // User 1 is a regular person
        $this->assertTrue(Object_User::isCreator(1, 2)); // User 2 is an admin
        $this->assertTrue(Object_User::isCreator(1, 3)); // User 3 is a worker

    }

}