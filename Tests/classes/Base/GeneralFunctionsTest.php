<?php
class Base_GeneralFunctionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testInitialize()
    {
        @$fail = new Base_GeneralFunctions();
    }
    
    public function testGetValue()
    {
        $this->assertTrue(Base_GeneralFunctions::getValue(array('test' => true), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(array(), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(array('test' => ''), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(null, 'test', false, true));
        $this->assertTrue(Base_GeneralFunctions::getValue(new SimpleObjectForTesting(), 'value', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(new SimpleObjectForTesting(), 'non-existant value', false, true));
    }
    
    public function testAsBoolean()
    {
        $this->assertTrue(Base_GeneralFunctions::asBoolean(true));
        $this->assertTrue(Base_GeneralFunctions::asBoolean('true'));
        $this->assertTrue(Base_GeneralFunctions::asBoolean(1));
        $this->assertTrue(Base_GeneralFunctions::asBoolean('yes'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean(false));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('false'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean(0));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('no'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('Anything Else!'));
    }
    
    public function testJsonSizing()
    {
        $this->assertTrue(Base_GeneralFunctions::sizeJson('') == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array())) == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array('Item' => 'Value'))) == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array('Item' => 'Value', 'Item2' => 'Value'))) == 2);
    }
    
    public function testJsonAdding()
    {
        $json = Base_GeneralFunctions::addJson('Some Data', 'More Data');
        $this->assertTrue($json == '["Some Data","More Data"]');        
        $json = Base_GeneralFunctions::addJson('', 'Some Data');
        $this->assertTrue($json == '["Some Data"]');
        $json = Base_GeneralFunctions::addJson($json, 'More Data');
        $this->assertTrue($json == '["Some Data","More Data"]');
        $json = Base_GeneralFunctions::addJson($json, 'More Data');
        $this->assertTrue($json == '["Some Data","More Data"]');
        $json = Base_GeneralFunctions::addJson($json, 'More Data', true);
        $this->assertTrue($json == '{"0":"Some Data","preferred":"More Data"}');
        $json = Base_GeneralFunctions::addJson($json, 'Even More Data', true);
        $this->assertTrue($json == '{"0":"Some Data","1":"More Data","preferred":"Even More Data"}');
    }
    
    public function testJsonDeleting()
    {
        $json = Base_GeneralFunctions::delJson('Some Data', 'Some Data');
        $this->assertTrue($json == 'Some Data');
        $json = '{"0":"Some Data","1":"More Data","preferred":"Even More Data"}';
        $json = Base_GeneralFunctions::delJson($json, 'Some Data');
        $this->assertTrue($json == '{"0":"More Data","preferred":"Even More Data"}');
    }
    
    public function testGetJson()
    {
        $this->assertTrue(Base_GeneralFunctions::getJson('Not Actually JSON') == array(0 => 'Not Actually JSON'));
        $json = json_encode(array('Some Data' => 'Stuff'));
        $this->assertTrue(Base_GeneralFunctions::getJson($json) == array('Some Data' => 'Stuff'));
        $json = json_encode(array('Some Data' => array('Stuff' => 'More Stuff')));
        $this->assertTrue(Base_GeneralFunctions::getJson($json) == array('Some Data' => array('Stuff' => 'More Stuff')));
    }
}

class SimpleObjectForTesting
{
    public $value = true;
}