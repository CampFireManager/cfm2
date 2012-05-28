<?php
class Object_UserauthTest extends PHPUnit_Framework_TestCase
{
    public function testObjectUserauthCreation()
    {
        $objUserauth = new Object_Userauth();
        $this->assertTrue(is_object($objUserauth));
    }
}