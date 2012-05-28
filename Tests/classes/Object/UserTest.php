<?php
class Object_UserTest extends PHPUnit_Framework_TestCase
{
    public function testObjectUserCreation()
    {
        $objUser = new Object_User();
        $this->assertTrue(is_object($objUser));
    }
}