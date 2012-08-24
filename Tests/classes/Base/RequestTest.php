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
            'REQUEST_URI' => '/service/talk/12/',
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
    
    public function parseServer($request)
    {
        $request->parse(
            $this->SarrGlobals,
            $this->SarrServer,
            $this->SarrRequest,
            $this->SarrGet,
            $this->SarrPost,
            $this->SarrFiles,
            $this->SstrInput
        );
    }

    public function parseFile($request)
    {
        $request->parse(
            $this->FarrGlobals,
            $this->FarrServer,
            $this->FarrRequest,
            $this->FarrGet,
            $this->FarrPost,
            $this->FarrFiles,
            $this->FstrInput
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
        $this->assertFalse($request->hasMediaType('dummy', 'text/html'));
    }

    public function testSimulatedServerConnection()
    {
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'get');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12/');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://localhost:1443/service/talk/12/');
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
        $this->assertTrue(is_array($request->get_arrRequestUrl()));
        $this->assertTrue($request->get_strPathSite() == 'service');
        $this->assertTrue($request->get_strPathRouter() == 'talk/12');
        $arrAcceptTypes = $request->get_arrAcceptTypes();
        $this->assertTrue(is_array($arrAcceptTypes));
        $this->assertTrue(count($arrAcceptTypes) == 3);
        $this->assertTrue($arrAcceptTypes['text/html'] == 1);
        $this->assertTrue($arrAcceptTypes['application/xhtml+xml'] == 1);
        $this->assertTrue($arrAcceptTypes['application/xml'] == "0.9");
    }

    public function testSimulatedServerWithNoSitePath()
    {
        $this->SarrServer['REQUEST_URI'] = '/';
        $this->SarrServer['SCRIPT_NAME'] = '/index.php';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_strPathSite() == '');
        $this->assertTrue($request->get_strPathRouter() == '');
    }

    public function testSimulatedHttpServerRequestOnNonStandardPort()
    {
        unset($this->SarrServer['HTTPS']);
        $this->SarrServer['SERVER_PORT'] = 8081;
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'http://localhost:8081/service/talk/12/');
    }

    public function testSimulatedServerConnectionUsingFormatExtensions()
    {
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.json';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.json');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/json');
        $this->assertTrue($request->get_strPathFormat() == 'json');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.atom';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.atom');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/atom+xml');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));


        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.pdf';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.pdf');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/pdf');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));


        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.ps';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.ps');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/postscript');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.rss';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.rss');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/rss+xml');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.soap';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.soap');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/soap+xml');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.xhtml';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.xhtml');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/xhtml+xml');
        $this->assertTrue($request->hasMediaType());
        $this->assertTrue($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.zip';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.zip');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/zip');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.tar.gz';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.tar.gz');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/x-gzip');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.mp3';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.mp3');
        $this->assertTrue($request->get_strPrefAcceptType() == 'audio/mpeg');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.m4a';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.m4a');
        $this->assertTrue($request->get_strPrefAcceptType() == 'audio/mp4');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.ogg';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.ogg');
        $this->assertTrue($request->get_strPrefAcceptType() == 'audio/ogg');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.png';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.png');
        $this->assertTrue($request->get_strPrefAcceptType() == 'image/png');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.jpg';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.jpg');
        $this->assertTrue($request->get_strPrefAcceptType() == 'image/jpeg');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.gif';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.gif');
        $this->assertTrue($request->get_strPrefAcceptType() == 'image/gif');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.svg';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.svg');
        $this->assertTrue($request->get_strPrefAcceptType() == 'image/svg+xml');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.css';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.css');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/css');
        $this->assertTrue($request->hasMediaType());
        $this->assertTrue($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.html';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.html');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/html');
        $this->assertTrue($request->hasMediaType());
        $this->assertTrue($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.csv';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.csv');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/csv');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.xml';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.xml');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/xml');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.txt';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.txt');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/plain');
        $this->assertTrue($request->hasMediaType());
        $this->assertTrue($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.vcd';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.vcd');
        $this->assertTrue($request->get_strPrefAcceptType() == 'text/vcard');
        $this->assertTrue($request->hasMediaType());
        $this->assertTrue($request->hasMediaType('site'));
        $this->assertTrue($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.ogv';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.ogv');
        $this->assertTrue($request->get_strPrefAcceptType() == 'video/ogg');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.avi';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.avi');
        $this->assertTrue($request->get_strPrefAcceptType() == 'video/mpeg');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.mp4';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.mp4');
        $this->assertTrue($request->get_strPrefAcceptType() == 'video/mp4');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.webm';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.webm');
        $this->assertTrue($request->get_strPrefAcceptType() == 'video/webm');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.wmv';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.wmv');
        $this->assertTrue($request->get_strPrefAcceptType() == 'video/x-ms-wmv');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.doc';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.doc');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/msword');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.docx';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.docx');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));
        
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.odt';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.odt');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.oasis.opendocument.text');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.xls';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.xls');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.ms-excel');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.xlsx';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.xlsx');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.ods';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.ods');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.oasis.opendocument.spreadsheet');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.ppt';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.ppt');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.ms-powerpoint');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.pptx';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.pptx');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.odp';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.odp');
        $this->assertTrue($request->get_strPrefAcceptType() == 'application/vnd.oasis.opendocument.presentation');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertTrue($request->hasMediaType('media'));

        $this->SarrServer['REQUEST_URI'] = '/service/talk/12.random';
        $request = new Base_Request();
        $this->parseServer($request);
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12.random');
        $this->assertTrue($request->get_strPrefAcceptType() == 'unknown/random');
        $this->assertFalse($request->hasMediaType());
        $this->assertFalse($request->hasMediaType('site'));
        $this->assertFalse($request->hasMediaType('rest'));
        $this->assertFalse($request->hasMediaType('media'));

    }
    
    public function testSimulatedServerConnectionWithHttpAuthorization()
    {
        $request = new Base_Request();
        $this->SarrServer['HTTP_AUTHORIZATION'] = 'basic:' . base64_encode('username:password');
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'get');
        $this->assertTrue($request->get_requestUrlFull() == 'https://username:password@localhost:1443/service/talk/12/');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://username:password@localhost:1443/service/talk/12/');
        $this->assertTrue($request->get_strUsername() == 'username');
        $this->assertTrue($request->get_strPassword() == 'password');
    }

    public function testSimulatedServerConnectionWithPhpAuth()
    {
        $request = new Base_Request();
        $this->SarrServer['PHP_AUTH_USER'] = 'username';
        $this->SarrServer['PHP_AUTH_PW'] = 'password';
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'get');
        $this->assertTrue($request->get_requestUrlFull() == 'https://username:password@localhost:1443/service/talk/12/');
        $this->assertTrue($request->get_requestUrlExParams() == 'https://username:password@localhost:1443/service/talk/12/');
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
        $this->parseServer($request);
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
        $this->parseServer($request);
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
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'post');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12/');
        $arrParameters = $request->get_arrRqstParameters();
        $this->assertTrue(count($arrParameters) == 3);
        $this->assertTrue($arrParameters['dostuff'] == '');
        $this->assertTrue($arrParameters['param'] == '1');
        $this->assertTrue(is_array($arrParameters['_FILES']));
    }

    public function testSimulatedServerConnectionWithPutCall()
    {
        $request = new Base_Request();
        $this->SarrServer['REQUEST_URI'] = '/service/talk/';
        $this->SarrServer['REQUEST_METHOD'] = 'PUT';
        $this->SstrInput = 'User=Person';
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'put');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/');
        $arrRqstParameters = $request->get_arrRqstParameters();
        $this->assertTrue(is_array($arrRqstParameters));
        $this->assertTrue(count($arrRqstParameters) == 1);
        $this->assertTrue($arrRqstParameters['User'] == 'Person');
    }

    public function testSimulatedServerConnectionWithDeleteCall()
    {
        $request = new Base_Request();
        $this->SarrServer['REQUEST_URI'] = '/service/talk/12/';
        $this->SarrServer['REQUEST_METHOD'] = 'DELETE';
        $this->parseServer($request);
        $this->assertTrue($request->get_strRequestMethod() == 'delete');
        $this->assertTrue($request->get_requestUrlFull() == 'https://localhost:1443/service/talk/12/');
    }

    
    public function testSimulatedFileConnection()
    {
        $request = new Base_Request();
        $this->parseFile($request);
        $this->assertTrue($request->get_strRequestMethod() == 'file');
        $this->assertTrue(strpos($request->get_requestUrlFull(), "/Tests/classes/Base/RequestTest.php") > 0);
        $this->assertTrue(strpos($request->get_requestUrlExParams(), "/Tests/classes/Base/RequestTest.php") > 0);
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
        $this->parseFile($request);
        $this->assertTrue(strpos($request->get_requestUrlFull(), "/bootstrap.php") > 0);
    }
}