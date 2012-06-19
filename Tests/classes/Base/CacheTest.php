<?php
class Base_CacheTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        Base_Cache::flush();
        $cache = Base_Cache::getHandler();
        $this->assertTrue(is_object($cache));
        $this->assertTrue(get_class($cache) == 'Base_Cache');
        $this->assertTrue(is_array($cache->arrCache));
        $this->assertTrue(count($cache->arrCache) == 0);
        $cache->arrCache['test'] = true;
        $this->assertTrue(count($cache->arrCache) == 1);
        Base_Cache::flush();
        $cache = Base_Cache::getHandler();
        $this->assertTrue(count($cache->arrCache) == 0);
    }
}