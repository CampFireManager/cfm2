<?php
class Object_TalkTest extends PHPUnit_Framework_TestCase
{
    public function testObjectTalkCreation()
    {
        $objTalk = new Object_Talk();
        $this->assertTrue(is_object($objTalk));
    }
}