<?php
class Object_RoomTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('unittest.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objRoom = new Object_Room_Demo();
        $objRoom->initializeDemo();
        $objResource = new Object_Resource_Demo();
        $objResource->initializeDemo();
    }
    
    public function testObjectRoomCreation()
    {
        $objRoom = new Object_Room();
        $this->assertTrue(is_object($objRoom));
    }
    
    public function testGetAllRooms()
    {
        $arrRooms = Object_Room::brokerAll();
        $this->assertTrue(count($arrRooms) == 3);
        $this->assertTrue(is_object($arrRooms[1]));
        $this->assertTrue($arrRooms[1]->getKey('strRoom') == 'Room A');
        $this->assertTrue($arrRooms[1]->getKey('intCapacity') == 100);
        $this->assertTrue($arrRooms[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrRooms[1]->getKey('jsonResourceList') == '[1,2]');
        $this->assertTrue($arrRooms[2]->getKey('strRoom') == 'Room B');
        $this->assertTrue($arrRooms[2]->getKey('intCapacity') == 50);
        $this->assertTrue($arrRooms[2]->getKey('isLocked') == 0);
        $this->assertTrue($arrRooms[2]->getKey('jsonResourceList') == '[2,3]');
        $arrRooms[3]->setFull(true);
        $arrRoom = $arrRooms[3]->getSelf();
        $this->assertTrue($arrRoom['strRoom'] == 'Room C');
        $this->assertTrue($arrRoom['intCapacity'] == 75);
        $this->assertTrue(count($arrRoom['arrResources']) == 1);
        $this->assertTrue(is_array($arrRoom['arrResources'][0]));
        $this->assertTrue($arrRoom['arrResources'][0]['strResource'] == 'Flat Screen TV');
    }
    
    public function testGetAllRoomsByRoomSize()
    {
        $arrRooms = Object_Room::brokerAllByRoomSize();
        $this->assertTrue(count($arrRooms) == 3);
        $this->assertTrue(is_object($arrRooms[0]));
        $this->assertTrue($arrRooms[0]->getKey('strRoom') == 'Room A');
        $this->assertTrue($arrRooms[0]->getKey('intCapacity') == 100);
        $this->assertTrue($arrRooms[0]->getKey('isLocked') == 1);
        $this->assertTrue($arrRooms[0]->getKey('jsonResourceList') == '[1,2]');
        $this->assertTrue($arrRooms[1]->getKey('strRoom') == 'Room C');
        $this->assertTrue($arrRooms[1]->getKey('intCapacity') == 75);
        $this->assertTrue($arrRooms[1]->getKey('isLocked') == 0);
        $this->assertTrue($arrRooms[1]->getKey('jsonResourceList') == '[3]');
        $arrRooms[2]->setFull(true);
        $arrRoom = $arrRooms[2]->getSelf();
        $this->assertTrue($arrRoom['strRoom'] == 'Room B');
        $this->assertTrue($arrRoom['intCapacity'] == 50);
        $this->assertTrue(count($arrRoom['arrResources']) == 2);
        $this->assertTrue(is_array($arrRoom['arrResources'][0]));
        $this->assertTrue($arrRoom['arrResources'][0]['strResource'] == 'PA');
        $this->assertTrue(is_array($arrRoom['arrResources'][1]));
        $this->assertTrue($arrRoom['arrResources'][1]['strResource'] == 'Flat Screen TV');
    }
}