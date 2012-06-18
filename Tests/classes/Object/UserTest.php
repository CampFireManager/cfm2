<?php
class Object_UserTest extends PHPUnit_Framework_TestCase
{
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
}