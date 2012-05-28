<?php
class Object_RoomTest extends PHPUnit_Framework_TestCase
{
    public function testObjectRoomCreation()
    {
        $objRoom = new Object_Room();
        $this->assertTrue(is_object($objRoom));
    }
}