<?php
class Object_TalkTest extends PHPUnit_Framework_TestCase
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
    }

    public function testObjectTalkCreation()
    {
        $objTalk = new Object_Talk();
        $this->assertTrue(is_object($objTalk));
        $data = $objTalk->getSelf();
        $this->assertTrue($data['intTalkID'] == null);
        $this->assertTrue($data['strTalk'] == null);
        $this->assertTrue($data['hasNsfwMaterial'] == null);
        $this->assertTrue($data['strTalkSummary'] == null);
        $this->assertTrue($data['intUserID'] == null);
        $this->assertTrue($data['intRequestedRoomID'] == null);
        $this->assertTrue($data['intRequestedSlotID'] == null);
        $this->assertTrue($data['intRoomID'] == "-1");
        $this->assertTrue($data['intSlotID'] == null);
        $this->assertTrue($data['intLength'] == "1");
        $this->assertTrue($data['jsonLinks'] == null);
        $this->assertTrue($data['isLocked'] == false);
        $this->assertTrue($data['jsonOtherPresenters'] == null);
        $this->assertTrue($objTalk->getKey('intTalkID') == null);
    }
    
    public function testBrokerByID()
    {
        $objTalk = Object_Talk::brokerByID(1);
        $data = $objTalk->getSelf();
        $this->assertTrue($data != false);
        $this->assertTrue($data['intTalkID'] == 1);
        $this->assertTrue($data['strTalk'] == "Keynote");
        $this->assertTrue($data['hasNsfwMaterial'] == null);
        $this->assertTrue($data['strTalkSummary'] == 'A welcome to Barcamps');
        $this->assertTrue($data['intUserID'] == 1);
        $this->assertTrue($data['intRequestedRoomID'] == 1);
        $this->assertTrue($data['intRequestedSlotID'] == 1);
        $this->assertTrue($data['intRoomID'] == 1);
        $this->assertTrue($data['intSlotID'] == 1);
        $this->assertTrue($data['intLength'] == 1);
        $json_data = json_decode($data['jsonLinks'], true);
        $this->assertTrue(count($json_data) == 2);
        $this->assertTrue($data['isLocked'] == 1);
        $json_data = json_decode($data['jsonOtherPresenters'], true);
        $this->assertTrue(count($json_data) == 0);
    }
    
    public function testUnscheduleATalkWithAndWithoutResettingintSlotID()
    {
        Object_User::isSystem(true);
        $objTalk = Object_Talk::brokerByID(1);
        $this->assertTrue(is_object($objTalk));
        $objTalk->unschedule();
        $data = $objTalk->getSelf();
        $this->assertTrue($data['intRoomID'] == -1);
        $this->assertTrue($data['intSlotID'] == 1);
        $this->assertTrue($data['isLocked'] == 0);
        $config = Container_Config::brokerByID('Schedule Only In This Slot', '1');
        if ($config->getKey('value') == '0') {
            $config->setKey('value', '1');
            $config->write();
        }
        $objTalk = Object_Talk::brokerByID(1);
        $this->assertTrue(is_object($objTalk));
        $objTalk->unschedule();
        $data = $objTalk->getSelf();
        $this->assertTrue($data['intRoomID'] == -1);
        $this->assertTrue($data['intSlotID'] == -1);
        $this->assertTrue($data['isLocked'] == 0);
        Object_User::isSystem(false);
    }
    
    public function testFixATalk()
    {
        Object_User::isSystem(true);
        $objTalk = Object_Talk::brokerByID(2);
        $this->assertTrue(is_object($objTalk));
        $data = $objTalk->getSelf();
        $this->assertTrue($data['isLocked'] == 0);
        $this->assertTrue($data['isRoomLocked'] == 0);
        $this->assertTrue($data['isSlotLocked'] == 0);
        $objTalk->fixTalk();
        $data = $objTalk->getSelf();
        $this->assertTrue($data['isLocked'] == 1);
        $this->assertTrue($data['isRoomLocked'] == 1);
        $this->assertTrue($data['isSlotLocked'] == 1);
        Object_User::isSystem(false);
    }
    
    public function testTalksSort_Baseline()
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
    }

    public function testTalksSort_SortWholeDayAtStartOfDay()
    {
        Object_User::isSystem(true);
        $arrTalks = Object_Talk::brokerAll();
        $config = Container_Config::brokerByID('Schedule Only In This Slot', '0');
        Object_Talk::lockTalks(date('Y-m-d ') . " 00:00:01");
        Object_Talk::sortAndPlaceTalksByAttendees(date('Y-m-d ') . " 00:00:01");
        Object_User::isSystem(false);

        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 0);
        // After sorting, room 1 can't be used (locked) and room 3 is larger
        // than room 2.
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') >= 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 0);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);
    }

    /**
     * @todo This should probably have the pre-2PM lock action performed on it
     * first. Another test or two to be written I suspect.
     */
    public function testTalksSort_SortWholeDayAt2PM()
    {
        Object_User::isSystem(true);
        $arrTalks = Object_Talk::brokerAll();
        $config = Container_Config::brokerByID('Schedule Only In This Slot', '0');
        Object_Talk::lockTalks(date('Y-m-d ') . " 14:00:00");
        Object_Talk::sortAndPlaceTalksByAttendees(date('Y-m-d ') . " 14:00:00");
        Object_User::isSystem(false);

        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);
    }

    public function testTalksSort_SortSlotOnlyAtStartOfDay()
    {
        Object_User::isSystem(true);
        $arrTalks = Object_Talk::brokerAll();
        $config = Container_Config::brokerByID('Schedule Only In This Slot', '0');
        $config->setKey('value', 1);
        Object_Talk::lockTalks(date('Y-m-d ') . " 00:00:01");
        Object_Talk::sortAndPlaceTalksByAttendees(date('Y-m-d ') . " 00:00:01");
        Object_User::isSystem(false);
        
        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 0);
        // After sorting, room 1 can't be used (locked) and room 3 is larger
        // than room 2.
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 0);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);
    }

    /**
     * @todo This should probably have the pre-2PM lock action performed on it
     * first. Another test or two to be written I suspect.
     */
    public function testTalksSort_SortSlotOnlyAt2PM()
    {
        Object_User::isSystem(true);
        $arrTalks = Object_Talk::brokerAll();
        Object_Talk::lockTalks(date('Y-m-d ') . " 14:00:00");
        Object_Talk::sortAndPlaceTalksByAttendees(date('Y-m-d ') . " 14:00:00");
        Object_User::isSystem(false);

        $this->assertTrue($arrTalks[1]->getKey('intRoomID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('intSlotID') == 1);
        $this->assertTrue($arrTalks[1]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[2]->getKey('intRoomID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[2]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[3]->getKey('intRoomID') == 3);
        $this->assertTrue($arrTalks[3]->getKey('intSlotID') == 2);
        $this->assertTrue($arrTalks[3]->getKey('isLocked') == 1);
        $this->assertTrue($arrTalks[4]->getKey('intRoomID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('intSlotID') == -1);
        $this->assertTrue($arrTalks[4]->getKey('isLocked') == 0);
    }
}
