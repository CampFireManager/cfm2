<?php
require_once dirname(__FILE__) . '/../../../classes/autoloader.php';

class Object_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testObjectConfigCreation()
    {
        $objConfig = new Object_Config();
        $this->assertTrue(is_object($objConfig));
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