<?php
class Object_SlotTest extends PHPUnit_Framework_TestCase
{
    public function testObjectSlotCreation()
    {
        $objSlot = new Object_Slot();
        $this->assertTrue(is_object($objSlot));
    }
}