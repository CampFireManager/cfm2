<?php
class Base_RequestTest extends PHPUnit_Framework_TestCase
{
    protected $FarrGlobals = null;
    protected $FarrServer = null;
    protected $FarrRequest = null;
    protected $FarrGet = null;
    protected $FarrPost = null;
    protected $FarrFiles = null;
    protected $FstrInput = null;

    protected $SarrGlobals = null;
    protected $SarrServer = null;
    protected $SarrRequest = null;
    protected $SarrGet = null;
    protected $SarrPost = null;
    protected $SarrFiles = null;
    protected $SstrInput = null;

    public function setUp()
    {
        Container_Request_Testable::reset();
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

        $this->FarrServer = array(
            "PHP_SELF" => '',
            "SCRIPT_NAME" => '',
            "SCRIPT_FILENAME" => '',
            "PATH_TRANSLATED" => '',
            "DOCUMENT_ROOT" => ''
        );
        $this->FarrGet = array();
        $this->FarrPost = array();
        if (count($this->FarrGet) > 0) {
            $this->FarrRequest = $this->FarrGet;
        } elseif (count($this->FarrPost) > 0) {
            $this->FarrRequest = $this->FarrPost;
        } else {
            $this->FarrRequest = array();
        }
        $this->FarrFiles = array();
        $this->FstrInput = "";
        $this->FarrGlobals = array(
            '_POST' => &$this->FarrPost, 
            '_GET' => &$this->FarrGet, 
            '_COOKIE' => array(), 
            '_FILES' => &$this->FarrFiles, 
            '_ENV' => array(), 
            '_REQUEST' => &$this->FarrRequest, 
            '_SERVER' => &$this->FarrServer,
            "argv" => array(
                0 => __FILE__,
                1 => "param=1",
                2 => "dostuff"
            ),
            "argc" => 2
        );

    }

    public function testCreateObject()
    {
        $request = new Base_Request();
        $this->assertTrue(is_object($request));
        $this->assertTrue(get_class($request) == 'Base_Request');
    }
    
    public function testHasMediaType()
    {
        $request = new Base_Request();
        $this->assertTrue($request->hasMediaType('site', 'text/html'));
        $this->assertTrue($request->hasMediaType('rest', 'text/html'));
        $this->assertFalse($request->hasMediaType('media', 'text/html'));
        $this->assertFalse($request->hasMediaType('dummy'));
        $this->assertFalse($request->hasMediaType('site', 'dummy/dummy'));
    }

    public function testSimulatedServerConnection()
    {
        $request = new Base_Request();
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
    
    public function testSimulatedServerConnectionWithHttpAuthorization()
    {
        $request = new Base_Request();
        $this->SarrServer['HTTP_AUTHORIZATION'] = 'basic:' . base64_encode('username:password');
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
        $this->assertTrue($request->get_requestUrlFull() == 'https://username:password@localhost:1443/service/talk/12');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://username:password@localhost:1443/service/talk/12');
        $this->assertTrue($request->get_strUsername() == 'username');
        $this->assertTrue($request->get_strPassword() == 'password');
    }

    public function testSimulatedServerConnectionWithPhpAuth()
    {
        $request = new Base_Request();
        $this->SarrServer['PHP_AUTH_USER'] = 'username';
        $this->SarrServer['PHP_AUTH_PW'] = 'password';
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
        $this->assertTrue($request->get_requestUrlFull() == 'https://username:password@localhost:1443/service/talk/12');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://username:password@localhost:1443/service/talk/12');
        $this->assertTrue($request->get_strUsername() == 'username');
        $this->assertTrue($request->get_strPassword() == 'password');
    }

    public function testSimulatedServerConnectionWithGetParameters()
    {
        $request = new Base_Request();
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12/?dostuff&param=1';
        $this->SarrGet = array(
            'dostuff' => '',
            'param' => '1'
        );
        $this->SarrRequest = &$this->SarrGet;
        $request->parse(
            $this->SarrGlobals,
            $this->SarrServer,
            $this->SarrRequest,
            $this->SarrGet,
            $this->SarrPost,
            $this->SarrFiles,
            $this->SstrInput
        );
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12/?dostuff&param=1');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://localhost:1443/service/talk/12/');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 2);
        $this->assertTrue($arrParameters['dostuff'] == '');
        $this->assertTrue($arrParameters['param'] == '1');
    }

    public function testSimulatedServerConnectionWithHeadParameters()
    {
        $request = new Base_Request();
        $this->SarrServer['HTTP_IF_MODIFIED_SINCE'] = gmdate('D, d M Y H:i:s \G\M\T', strtotime('2012-01-01')) . ';apparently sometimes some data appears here';
        $this->SarrServer['HTTP_IF_NONE_MATCH'] = '"' . sha1('somecontent') . '", W/"' . sha1('someothercontent') . '"';
        $this->SarrServer['REQUEST_METHOD'] = 'HEAD';
        $request->parse(
            $this->SarrGlobals,
            $this->SarrServer,
            $this->SarrRequest,
            $this->SarrGet,
            $this->SarrPost,
            $this->SarrFiles,
            $this->SstrInput
        );
        $this->assertTrue($request->get_strRequestMethod() == 'head');
        $this->assertTrue($request->get_hasIfModifiedSince() == 'Sun, 01 Jan 2012 00:00:00 GMT');
        $arrIfNoneMatch = $request->get_hasIfNoneMatch();
        $this->assertTrue($arrIfNoneMatch[0] == sha1('somecontent'));
        $this->assertTrue($arrIfNoneMatch[1] == sha1('someothercontent'));
    }

    public function testSimulatedServerConnectionWithPostParameters()
    {
        $request = new Base_Request();
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12/';
        $this->SarrServer['REQUEST_METHOD'] = 'POST';
        $this->SarrPost = array(
            'dostuff' => '',
            'param' => '1'
        );
        $this->SarrFiles = array(
            array(
                "file" => array(
                    "name" => "a_file",
                    "type" => "application/octet-stream",
                    "tmp_name" => "/tmp/phphwKqWs",
                    "error" => 0,
                    "size" => 2237
                )
            )
        );
        $this->SarrRequest = &$this->SarrPost;
        $request->parse(
            $this->SarrGlobals,
            $this->SarrServer,
            $this->SarrRequest,
            $this->SarrGet,
            $this->SarrPost,
            $this->SarrFiles,
            $this->SstrInput
        );
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12/');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://localhost:1443/service/talk/12/');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 3);
        $this->assertTrue($arrParameters['dostuff'] == '');
        $this->assertTrue($arrParameters['param'] == '1');
        $this->assertTrue(is_array($arrParameters['_FILES']));
    }

    
    public function testSimulatedFileConnection()
    {
        $request = new Base_Request();
        $request->parse(
            $this->FarrGlobals,
            $this->FarrServer,
            $this->FarrRequest,
            $this->FarrGet,
            $this->FarrPost,
            $this->FarrFiles,
            $this->FstrInput
        );
        $this->assertTrue($request->get_strRequestMethod() == 'file');
        $this->assertTrue($request->get_requestUrlFull() == "file:///var/www/cfm2/Tests/classes/Base/RequestTest.php");
        $this->assertTrue($request->get_requestUrlExParams() == "file:///var/www/cfm2/Tests/classes/Base/RequestTest.php");
        $this->assertTrue($request->get_strUsername() == null);
        $this->assertTrue($request->get_strPassword() == null);
        $this->assertTrue($request->get_strUserAgent() == null);
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/html');
        $arrPathItems = $request->get_arrPathItems();
        $this->assertTrue(count($arrPathItems) > 0);
        $this->assertTrue($arrPathItems[count($arrPathItems) - 1] == 'RequestTest.php');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 2);
        $this->assertTrue($arrParameters['param'] == "1");
        $this->assertTrue($arrParameters['dostuff'] == '');
        $this->assertTrue($request->get_strBasePath() == 'file:///');
    }

    public function testSimulatedFileConnectionWithNoPath()
    {
        $request = new Base_Request();
        $this->FarrGlobals['argv'][0] = 'bootstrap.php';
        $request->parse(
            $this->FarrGlobals,
            $this->FarrServer,
            $this->FarrRequest,
            $this->FarrGet,
            $this->FarrPost,
            $this->FarrFiles,
            $this->FstrInput
        );
        $this->assertTrue($request->get_requestUrlFull() == "file:///var/www/cfm2/Tests/bootstrap.php");
    }
}