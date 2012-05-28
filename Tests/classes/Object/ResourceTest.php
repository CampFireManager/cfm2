<?php
class Object_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function testObjectResourceCreation()
    {
        $objResource = new Object_Resource();
        $this->assertTrue(is_object($objResource));
    }
}