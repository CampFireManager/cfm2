<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class provides all the functions which are needed by code in the site
 * but which don't fit into more specific classes.
 *
 * @category Base_Response
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_Response
{
    protected static $httpStatusCodes = Array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => '(Unused)',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported'
    );
    protected static $handler = null;
    protected $intGenerationTime = null;
    
    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    protected static function getHandler()
    {
        if (self::$handler == null) {
            self::$handler = new self();
        }
        return self::$handler;
    }
    
    /**
    * A helper function to ensure pages that require authentication, get them.
    *
    * @return void
    */
    public static function requireAuth()
    {
        $objRequest = Container_Request::getRequest();
        if ($objRequest->get_strUsername() == null) {
            Base_Response::sendHttpResponse(401);
        }
    }

    /**
     * Send a correctly formatted HTTP response to a request
     *
     * @param integer $status       HTTP response code
     * @param string  $body         Message to be sent
     * @param string  $contentType MIME type to send
     * @param string  $extra        Additional information beyond the routine HTTP status message
     *
     * @return void
     */
    public static function sendHttpResponse($status = 200, $body = null, $contentType = 'text/html', $extra = '')
    {        

        // Is there something for us to send
        if (($body != '' && $body != null) || $contentType != 'text/html') {
            // We'll send the $body next
        } else {
            // Let's make an appropriate response.
            $message = '';
            switch($status) {
            case 204:
                // This means "No content", so it would be rather foolish to send content now.
                break;
            case 401:
                header('WWW-Authenticate: Basic realm="Authentication Required"');
                $message = 'You must be authorized to view this page.';
                break;
            case 404:
                $temp = static::getPath();
                $message = 'The requested URL ' . $temp[0] . ' was not found.';
                break;
            case 500:
                $message = 'The server encountered an error processing your request.';
                break;
            case 501:
                $message = 'The requested method is not implemented.';
                break;
            }

            // Again, don't send any text if the response is to send no further content
            if ($status != 204) {
                // Send the stock message
                $messageContent = "<p>{$message}</p>";
                // Add extra padding if required
                if ($extra != '') {
                    $messageContent .= "\r\n    <p>$extra</p>";
                }
                // Here's the actual content to send.
                $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
' .                     '<html>
' .                     '  <head>
' .                     '    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
' .                     '    <title>' . $status . ' ' . static::$httpStatusCodes[$status] . '</title>
' .                     '  </head>
' .                     '  <body>
' .                     '    <h1>' . static::$httpStatusCodes[$status] . '</h1>
' .                     '    ' . $messageContent . '
' .                     '  </body>
' .                     '</html>';
            }
        }
        
        // Because we don't track when content was last changed (should we? perhaps!)
        // instead, I'm using the etag header to only send content that doesn't
        // match the If-None-Match header.
        // This information was compiled using a lot of information at StackOverflow:
        // This gave me a lot of detail about what I would expect to see in the
        // If-None-Match header.
        // http://stackoverflow.com/q/2086712/5738
        // This was where I thought about using preg_match_all to match the INM
        // header
        // http://stackoverflow.com/a/2001482/5738
        //
        // I might need to turn this off, given some of the comments on the SO site!
        
        
        $objRequest = Container_Request::getRequest();
        $thisetag = sha1($objRequest->get_requestUrlExParams() . $body);
        header("ETag: \"$thisetag\"");
        foreach ($objRequest->get_hasIfNoneMatch() as $etag) {
            if ($thisetag == $etag || 'W/ ' . $thisetag == $etag) {
                header('HTTP/1.1 304 ' . static::$httpStatusCodes[304]);
                exit(0);
            }
        }
        
        // Send the relevant headers for this type of response
        header('HTTP/1.1 ' . $status . ' ' . static::$httpStatusCodes[$status]);
        header('Content-type: ' . $contentType);
        echo $body;
        exit(0);
    }

    /**
     * Send an extended, yet still correctly formatted HTTP response to a request
     *
     * @param integer $status HTTP response code
     * @param string  $extra  Additional information beyond the routine HTTP status message
     *
     * @return void
     */
    public static function sendHttpResponseNote($status = 200, $extra = '')
    {
        Base_Response::sendHttpResponse($status, null, 'text/html', $extra);
    }

    /**
     * Return the string associated to an HTTP status code or false if it's wrong
     *
     * @param integer $status HTTP status code
     *
     * @return string|false The string associated to this code, or false if the code doesn't exist.
     */
    public static function returnHttpResponseString($status = 200)
    {
        if (isset(self::$httpStatusCodes[$status])) {
            return self::$httpStatusCodes[$status];
        } else {
            return false;
        }
    }

    /**
     * Provide a downloadable file and exit the script
     *
     * @param string  $file       File to send
     * @param boolean $isResumable  Can we supply headers to make this file resumable?
     * @param string  $mediaType The Internet Media Type for this media. If unset, force the download in browsers.
     *
     * @return void
     *
     * @link http://www.php.net/manual/en/function.fread.php#84115
     */
    function sendResumableFile($file, $isResumable = TRUE, $mediaType = 'application/force-download')
    {
        //First, see if the file exists
        if (!is_file($file)) {
            static::sendHttpResponse(404);
        }

        //Gather relevent info about file
        $size = filesize($file);
        $fileinfo = pathinfo($file);

        //workaround for IE filename bug with multiple periods / multiple dots in filename
        //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
        $filename = (strstr(Base_GeneralFunctions::getValue($_SERVER, 'HTTP_USER_AGENT', ''), 'MSIE')) ?
        preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
        $fileinfo['basename'];

        //check if http_range is sent by browser (or download manager)
        if ($isResumable && isset($_SERVER['HTTP_RANGE'])) {
            list($unitSize, $originalRange) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if ($unitSize == 'bytes') {
                // According to the spec, you could request several ranges here.
                // For simplicity, just send the first one.
                $ranges = explode(',', $originalRange);
                $range = $ranges[0];
            } else {
                $range = '';
            }
        } else {
            $range = '';
        }

        //figure out download piece from range (if set)
        $seek = explode('-', $range, 2);
        if (isset($seek[0])) {
            $seekStart = $seek[0];
        } else {
            $seekStart = '';
        }
        if (isset($seek[1])) {
            $seekEnd = $seek[1];
        } else {
            $seekEnd = '';
        }

        //set start and end based on range (if set), else set defaults
        //also check for invalid ranges.
        $seekEnd = (empty($seekEnd)) ? ($size - 1) : min(abs(intval($seekEnd)), ($size - 1));
        $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ? 0 : max(abs(intval($seekStart)), 0);

        //add headers if resumable
        if ($isResumable) {
            //Only send partial content header if downloading a piece of the file (IE workaround)
            if ($seekStart > 0 || $seekEnd < ($size - 1)) {
                header('HTTP/1.1 206 Partial Content');
            }

            header('Accept-Ranges: bytes');
            header('Content-Range: bytes '.$seekStart.'-'.$seekEnd.'/'.$size);
        }

        header('Content-Type: ' . $mediaType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: '.($seekEnd - $seekStart + 1));

        //open the file
        $filepointer = fopen($file, 'rb');
        //seek to start of missing part
        fseek($filepointer, $seekStart);

        //start buffered download
        while (!feof($filepointer)) {
            //reset time limit for big files
            set_time_limit(0);
            print(fread($filepointer, 1024*8));
            flush();
            ob_flush();
        }

        fclose($filepointer);
        exit;
    }
    
    /**
    * Do a redirection to the $newPage (relative to the base URI of the site)
    *
    * @param string $newPage New page to refer to
    *
    * @return void
    */
    public static function redirectTo($newPage = '')
    {
        $objRequest = Container_Request::getRequest();
        $redirectUrl = $objRequest->get_strBasePath();
        if (substr($redirectUrl, -1) == '/') {
            $redirectUrl = substr($redirectUrl, 0, -1);
        }
        if (substr($newPage, 0, 1) != '/') {
            $redirectUrl .= '/';
        }
        $redirectUrl .= $newPage;
        header("Location: $redirectUrl");
        exit(0);
    }
    
    public static function getGenerationTime($intStopTime = null)
    {
        if ($intStopTime == null) {
            $intStopTime = microtime(true);
        }
        $handler = self::getHandler();
        if ($handler->intGenerationTime == null) {
            throw new LogicException("Generation Time not started.");
        }
        return $intStopTime - $handler->intGenerationTime;
    }
    
    public static function setGenerationTime($intStartTime = null)
    {
        if ($intStartTime == null) {
            $intStartTime = microtime(true);
        }
        $handler = self::getHandler();
        if ($handler->intGenerationTime != null) {
            throw new LogicException("Generation Time already started.");
        }
        $handler->intGenerationTime = $intStartTime;
    }
    
    /**
     * This function ensures we've got the Smarty library loaded, and then
     * starts the template associated to it.
     *
     * @param string $template       Template to load
     * @param array  $arrAssignments Variables to be assigned to the template
     *
     * @return void
     */
    public static function render($template = '', $arrAssignments = array())
    {
        $libSmarty = Base_ExternalLibraryLoader::loadLibrary("Smarty");
        if ($libSmarty == false) {
            die("Failed to load Smarty");
        }
        $libSmarty .= '/libs/Smarty.class.php';
        $baseSmarty = dirname(__FILE__) . '/../../SmartyTemplates/';
        $enableSmartyDebugging = (Container_Config::brokerByID('smarty_debug', 'true')->getKey('value'));
        include_once $libSmarty;
        $objSmarty = new Smarty();
        if ($enableSmartyDebugging) {
            $objSmarty->debugging = true;
        }
        $objSmarty->setTemplateDir($baseSmarty . 'Source');
        $objSmarty->setCompileDir(
            Container_Config::brokerByID('TemporaryFiles', '/tmp')->getKey('value') . '/smartyCompiled'
        );
        if (is_array($arrAssignments) and count($arrAssignments) > 0) {
            foreach ($arrAssignments as $key=>$value) {
                $objSmarty->assign($key, $value);
            }
        }
        foreach (Container_Config::brokerAll() as $key=>$object) {
            switch ($key) {
                case 'RW_DSN':
                case 'RW_User':
                case 'RW_Pass':
                case 'RO_DSN':
                case 'RO_User':
                case 'RO_Pass':
                case 'DatabaseType':
                    break;
                default:
                    $config[$key] = $object->getKey('value');
            }
            
        }
        $objRequest = Container_Request::getRequest();
        $config['baseurl'] = $objRequest->get_strBasePath();
        $objSmarty->assign('SiteConfig', $config);
        $objSmarty->assign('PageGenerationTime', self::getGenerationTime());
        if (file_exists($baseSmarty . 'Source/' . $template . '.html.tpl')) {
            $objSmarty->display($template . '.html.tpl');
        } else {
            $objSmarty->display('Generic_Object.html.tpl');
        }
    }
}