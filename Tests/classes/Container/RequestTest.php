<?php
class Container_RequestTest extends PHPUnit_Framework_TestCase
{
    public function testSimulatedServerConnection()
    {
        $arrServer = array(
            'REQUEST_METHOD' => 'GET',
            'HTTPS' => 1,
            'HTTP_AUTHORIZATION' => 'basic:' . base64_encode('username:password'),
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/service/talk/12?param=1',
            'SCRIPT_NAME' => '/service/index.php',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0'
        );
        $arrGet = array(
            'param' => "1"
        );
        $arrPost = array();
        if (count($arrGet) > 0) {
            $arrRequest = $arrGet;
        } elseif (count($arrPost) > 0) {
            $arrRequest = $arrPost;
        } else {
            $arrRequest = array();
        }
        $arrFiles = array();
        $strInput = "";
        $arrGlobals = array(
            '_POST' => $arrPost, 
            '_GET' => $arrGet, 
            '_COOKIE' => array(), 
            '_FILES' => $arrFiles, 
            '_ENV' => array(), 
            '_REQUEST' => $arrRequest, 
            '_SERVER' => $arrServer
        );
        $request = Container_Request_Testable::GetHandler();
        $this->assertTrue(is_object($request));
        $request->parse($arrGlobals, $arrServer, $arrRequest, $arrGet, $arrPost, $arrFiles, $strInput);
        $this->assertTrue($request->get_strRequestMethod() == 'get');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost/service/talk/12?param=1');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://localhost/service/talk/12');
        $this->assertTrue($request->get_strUsername() == 'username');
        $this->assertTrue($request->get_strPassword() == 'password');
        $this->assertTrue($request->get_strUserAgent() == 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/html');
        $arrPathItems = $request->get_arrPathItems();
        $this->assertTrue(count($arrPathItems) == 2);
        $this->assertTrue($arrPathItems[0] == 'talk');
        $this->assertTrue($arrPathItems[1] == '12');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 1);
        $this->assertTrue($arrParameters['param'] == 1);
        $this->assertTrue($request->get_strBasePath() == 'https://localhost/service');
    }

    public function testSimulatedFileConnection()
    {
        $arrServer = array(
            "PHP_SELF" => '',
            "SCRIPT_NAME" => '',
            "SCRIPT_FILENAME" => '',
            "PATH_TRANSLATED" => '',
            "DOCUMENT_ROOT" => ''
        );
        $arrGet = array();
        $arrPost = array();
        if (count($arrGet) > 0) {
            $arrRequest = $arrGet;
        } elseif (count($arrPost) > 0) {
            $arrRequest = $arrPost;
        } else {
            $arrRequest = array();
        }
        $arrFiles = array();
        $strInput = "";
        $arrGlobals = array(
            '_POST' => $arrPost, 
            '_GET' => $arrGet, 
            '_COOKIE' => array(), 
            '_FILES' => $arrFiles, 
            '_ENV' => array(), 
            '_REQUEST' => $arrRequest, 
            '_SERVER' => $arrServer,
            "argv" => array(
                0 => __FILE__
            ),
            "argc" => 1
        );
        $request = Container_Request_Testable::GetHandler();
        $this->assertTrue(is_object($request));
        $request->parse($arrGlobals, $arrServer, $arrRequest, $arrGet, $arrPost, $arrFiles, $strInput);
        $this->assertTrue($request->get_strRequestMethod() == 'file');
    }
}

class Container_Request_Testable extends Container_Request
{
    public static function GetHandler()
    {
        return parent::GetHandler();
    }
    
    public static function reset()
    {
        parent::reset();
    }
}