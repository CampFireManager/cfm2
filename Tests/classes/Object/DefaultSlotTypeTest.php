<?php
class Object_DefaultSlotTypeTest extends PHPUnit_Framework_TestCase
{
    public function testObjectDefaultSlotTypeCreation()
    {
        $objConfig = new Object_DefaultSlotType();
        $this->assertTrue(is_object($objConfig));
    }
}