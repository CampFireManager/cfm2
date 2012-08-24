<?php
class Object_UserTest extends PHPUnit_Framework_TestCase
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
        $objUser = new Object_User_Demo();
        $objUser->initializeDemo();
        $objUserAuth = new Object_UserAuth_Demo();
        $objUserAuth->initializeDemo();
        Base_Cache::flush();
    }

    public function testObjectUserCreation()
    {
        $objUser = new Object_User();
        $this->assertTrue(is_object($objUser));
    }
        
    public function testSystemUser()
    {
        $this->assertFalse(Object_User::isSystem());
        $this->assertTrue(Object_User::isSystem(true));
        $this->assertTrue(Object_User::isSystem());
        $this->assertFalse(Object_User::isSystem(false));
        $this->assertFalse(Object_User::isSystem());
    }

    public function testIsAdmin()
    {
        $this->assertFalse(Object_User::isAdmin());
        $this->assertFalse(Object_User::isCreator());
        Object_User::isSystem(true);
        $this->assertTrue(Object_User::isAdmin());
        $this->assertTrue(Object_User::isCreator());
        Object_User::isSystem(false);
        $this->assertFalse(Object_User::isAdmin(3)); // User 3 is a worker
        $this->assertTrue(Object_User::isAdmin(2)); // User 2 is an admin
        $this->assertFalse(Object_User::isAdmin(1)); // User 1 is a regular person
        $this->assertTrue(Object_User::isCreator(1, 1));
        $this->assertFalse(Object_User::isCreator(2, 1)); // User 1 is a regular person
        $this->assertTrue(Object_User::isCreator(1, 2)); // User 2 is an admin
        $this->assertTrue(Object_User::isCreator(1, 3)); // User 3 is a worker
    }

    public function testNewUser()
    {
        $SarrServer = array(
            'REQUEST_METHOD' => 'GET',
            'HTTPS' => 1,
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '1443',
            'REQUEST_URI' => '/service/talk/12/',
            'SCRIPT_NAME' => '/service/index.php',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0'
        );
        $SarrGet = array(
            'username' => 'aTestUser',
            'password' => 'aPassword',
            'register' => 'true'
        );
        $SarrPost = array();
        if (count($SarrGet) > 0) {
            $SarrRequest = $SarrGet;
        } elseif (count($SarrPost) > 0) {
            $SarrRequest = $SarrPost;
        } else {
            $SarrRequest = array();
        }
        $SarrFiles = array();
        $SstrInput = "";
        $SarrGlobals = array(
            '_POST' => &$SarrPost, 
            '_GET' => &$SarrGet, 
            '_COOKIE' => array(), 
            '_FILES' => &$SarrFiles, 
            '_ENV' => array(), 
            '_REQUEST' => &$SarrRequest, 
            '_SERVER' => &$SarrServer
        );

        Container_Request_Testable::reset();
        $request = Container_Request_Testable::getRequest(
            $SarrGlobals,
            $SarrServer,
            $SarrRequest,
            $SarrGet,
            $SarrPost,
            $SarrFiles,
            $SstrInput
        );
        $this->assertTrue(get_class($request) == 'Base_Request');
        $objUser = new Object_User(true);
        $this->assertTrue($objUser->getKey('intUserID') == 5);
        $this->assertTrue($objUser->getKey('strUser') == 'Anonymous');
        $this->assertFalse($objUser->getKey('isWorker'));
        $this->assertFalse($objUser->getKey('isAdmin'));
    }
}