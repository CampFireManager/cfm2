<?php
class Object_TrackTest extends PHPUnit_Framework_TestCase
{
    public function testObjectTrackCreation()
    {
        $objTrack = new Object_Track();
        $this->assertTrue(is_object($objTrack));
    }
}