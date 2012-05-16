<?php
class Base_OpenIDTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $openid = new Base_OpenID();
        $this->assertTrue(is_object($openid));
        $this->assertTrue(get_class($openid) == 'Base_OpenID');
    }
}