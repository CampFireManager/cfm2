<?php
class Object_SecureConfigTest extends PHPUnit_Framework_TestCase
{
    public function testObjectSecureConfigCreation()
    {
        $objSecureConfig = new Object_SecureConfig();
        $this->assertTrue(is_object($objSecureConfig));
    }
    
    public function testSetAndGetKeys()
    {
        $objSecureConfig = new Object_SecureConfig();
        $objSecureConfig->setKey('key', 'Demo');
        $objSecureConfig->setKey('value', true);
        $this->assertTrue('Demo' == $objSecureConfig->getKey('key'));
        $this->assertTrue($objSecureConfig->getKey('value'));
    }
}