<?php
class Base_CacheTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $cache = new Base_Cache();
        $this->assertTrue(is_object($cache));
        $this->assertTrue(get_class($cache) == 'Base_Cache');
    }
}