<?php
class Plugin_LimboTalksTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Base_Cache::flush();
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('unittest.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objAttendee = new Object_Attendee_Demo();
        $objAttendee->initializeDemo();
        $objDefaultSlotType = new Object_DefaultSlotType_Demo();
        $objDefaultSlotType->initializeDemo();
        $objResource = new Object_Resource_Demo();
        $objResource->initializeDemo();
        $objRoom = new Object_Room_Demo();
        $objRoom->initializeDemo();
        $objSlot = new Object_Slot_Demo();
        $objSlot->initializeDemo();
        $objTalk = new Object_Talk_Demo();
        $objTalk->initializeDemo();
        $objUser = new Object_User_Demo();
        $objUser->initializeDemo();
        $objUserauth = new Object_Userauth_Demo();
        $objUserauth->initializeDemo();
    }
    
    public function testHookCronTick()
    {
        $arrTalks = Object_Talk::brokerAll();
        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 0);
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 0);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);

        $plugin = new Plugin_LimboTalks();
        Object_User::isSystem(true);
        Object_Talk::lockTalks(date('Y-m-d ') . " 00:00:01");
        $plugin->hook_cronTick(date('Y-m-d ') . " 00:00:01");
        Object_User::isSystem(false);

        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 0);
        // After sorting, room 1 can't be used (locked) and room 3 is larger
        // than room 2.
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') >= 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 0);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == 3);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);
    }
}