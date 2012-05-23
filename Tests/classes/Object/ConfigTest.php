<?php
class Object_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testObjectConfigCreation()
    {
        $objConfig = new Object_Config();
        $this->assertTrue(is_object($objConfig));
    }
    
    public function testCreateLocalObject()
    {
        $objConfig = new Object_Config(array('key' => 'Demo', 'value' => true));
        $this->assertTrue('Demo' == $objConfig->getKey('key'));
        $this->assertTrue($objConfig->getKey('value'));
    }

    public function testSetAndGetKeys()
    {
        $objConfig = new Object_Config();
        $objConfig->setKey('key', 'Demo');
        $objConfig->setKey('value', true);
        $this->assertTrue('Demo' == $objConfig->getKey('key'));
        $this->assertTrue($objConfig->getKey('value'));
    }
}