<?php
class Container_RequestTest extends PHPUnit_Framework_TestCase
{
    protected $SarrGlobals = null;
    protected $SarrServer = null;
    protected $SarrRequest = null;
    protected $SarrGet = null;
    protected $SarrPost = null;
    protected $SarrFiles = null;
    protected $SstrInput = null;

    public function setUp()
    {
        $this->SarrServer = array(
            'REQUEST_METHOD' => 'GET',
            'HTTPS' => 1,
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '1443',
            'REQUEST_URI' => '/service/talk/12',
            'SCRIPT_NAME' => '/service/index.php',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0'
        );
        $this->SarrGet = array();
        $this->SarrPost = array();
        if (count($this->SarrGet) > 0) {
            $this->SarrRequest = $this->SarrGet;
        } elseif (count($this->SarrPost) > 0) {
            $this->SarrRequest = $this->SarrPost;
        } else {
            $this->SarrRequest = array();
        }
        $this->SarrFiles = array();
        $this->SstrInput = "";
        $this->SarrGlobals = array(
            '_POST' => &$this->SarrPost, 
            '_GET' => &$this->SarrGet, 
            '_COOKIE' => array(), 
            '_FILES' => &$this->SarrFiles, 
            '_ENV' => array(), 
            '_REQUEST' => &$this->SarrRequest, 
            '_SERVER' => &$this->SarrServer
        );
    }
    
    public function testInitialize()
    {
        Container_Request_Testable::reset();
        $request = Container_Request_Testable::GetHandler();
        $this->assertTrue(get_class($request) == 'Base_Request');
        $request->parse(
            $this->SarrGlobals,
            $this->SarrServer,
            $this->SarrRequest,
            $this->SarrGet,
            $this->SarrPost,
            $this->SarrFiles,
            $this->SstrInput
        );
        $this->assertTrue($request->get_strRequestMethod() == 'get');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://localhost:1443/service/talk/12');
        $this->assertTrue($request->get_strUsername() == null);
        $this->assertTrue($request->get_strPassword() == null);
        $this->assertTrue($request->get_strUserAgent() == 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/html');
        $arrPathItems = $request->get_arrPathItems();
        $this->assertTrue(count($arrPathItems) == 2);
        $this->assertTrue($arrPathItems[0] == 'talk');
        $this->assertTrue($arrPathItems[1] == '12');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 0);
        $this->assertTrue($request->get_strBasePath() == 'https://localhost:1443/service/');
        $this->assertTrue($request->hasMediaType());
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