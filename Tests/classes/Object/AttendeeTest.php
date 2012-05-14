<?php
class Object_AttendeeTest extends PHPUnit_Framework_TestCase
{
    public function testObjectAttendeeCreation()
    {
        $objConfig = new Object_Attendee();
        $this->assertTrue(is_object($objConfig));
    }
}