<?php
require_once dirname(__FILE__) . '/../../../classes/autoloader.php';

class Base_RequestTest extends PHPUnit_Framework_TestCase
{
    public function testSimulatedServerConnection()
    {
        $arrServer = array(
            'REQUEST_METHOD' => 'GET',
            'HTTPS' => 1,
            'HTTP_AUTHORIZATION' => base64_encode('basic:username:password'),
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/talk/12',
            'SCRIPT_NAME' => '/index.php',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0'
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
        $arrGlobals = array('_POST' => $arrPost, '_GET' => $arrGet, '_COOKIE' => array(), '_FILES' => $arrFiles, '_ENV' => array(), '_REQUEST' => $arrRequest, '_SERVER' => $arrServer);
        $request = Base_Request::getRequest($arrGlobals, $arrServer, $arrRequest, $arrGet, $arrPost, $arrFiles, $strInput);
        $this->assertTrue(is_array($request));
    }
}