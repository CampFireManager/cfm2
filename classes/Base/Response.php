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
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
/**
 * This class provides all the functions which are needed by code in the site
 * but which don't fit into more specific classes.
 *
 * @category Base_Response
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
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
    protected $floatGenerationTime = null;
    protected $lastModifiedTime = null;
    
    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return Base_Response
     */
    protected static function getHandler()
    {
        if (self::$handler == null) {
            self::$handler = new self();
        }
        return self::$handler;
    }
    
    /**
     * An internal function to return the last modified time for the page.
     *
     * @return integer|epoch
     */
    protected static function getLastModified()
    {
        $self = self::getHandler();
        if ($self->lastModifiedTime == null) {
            return strtotime('now');
        } else {
            return $self->lastModifiedTime;
        }
    }
    
    /**
     * A function to accumulate the last modified time for the page
     *
     * @param mixed $time The time string or integer to 
     * 
     * @return void
     */
    public static function setLastModifiedTime($time)
    {
        $self = self::getHandler();
        if (preg_match('/\d+-\d+-\d+ \d+:\d+:\d+/', $time) > 0
            || preg_match('/\d+-\d+-\d+ \d+:\d+/', $time) > 0
            || preg_match('/\d+-\d+-\d+/', $time) > 0
            || preg_match('/\d+:\d+:\d+ \d+-\d+-\d+/', $time) > 0
            || preg_match('/\d+:\d+ \d+-\d+-\d+/', $time) > 0
        ) {
            $time = strtotime($time);
        }
        if ($self->lastModifiedTime > $time) {
            $self->lastModifiedTime = $time;
        }
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
     * @param integer $status      HTTP response code
     * @param string  $body        Message to be sent
     * @param string  $contentType MIME type to send
     * @param string  $extra       Additional information beyond the routine HTTP status message
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
                $request = Container_Request::getRequest();
                $message = 'The requested URL ' . $request->get_requestUrlExParams() . ' was not found.';
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
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", self::getLastModified()) . " GMT");
        header("ETag: \"$thisetag\"");
        $arrETag = $objRequest->get_hasIfNoneMatch();
        if (is_array($arrETag)) {
            foreach ($arrETag as $etag) {
                if ($thisetag == $etag || 'W/ ' . $thisetag == $etag) {
                    header('HTTP/1.1 304 ' . static::$httpStatusCodes[304]);
                    exit(0);
                }
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
     * @param string  $file        File to send
     * @param boolean $isResumable Can we supply headers to make this file resumable?
     * @param string  $mediaType   The Internet Media Type for this media. If unset, force the download in browsers.
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

        if ($size < 4*1024*1024) { // 4Mb
            $content = file_get_contents($file);
            $objRequest = Container_Request::getRequest();
            $thisetag = sha1($objRequest->get_requestUrlExParams() . $content);
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($file)) . " GMT");
            header("ETag: \"$thisetag\"");
            $arrETag = $objRequest->get_hasIfNoneMatch();
            if (is_array($arrETag)) {
                foreach ($arrETag as $etag) {
                    if ($thisetag == $etag || 'W/ ' . $thisetag == $etag) {
                        header('HTTP/1.1 304 ' . static::$httpStatusCodes[304]);
                        exit(0);
                    }
                }
            }
        }

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
    
    /**
     * Return the calculated value for the page generation
     *
     * @param integer $floatStopTime The time the page generation counter is
     * stopped - usually as the last action on the page.
     * 
     * @return float 
     */
    public static function getGenerationTime($floatStopTime = null)
    {
        if ($floatStopTime == null) {
            $floatStopTime = microtime(true);
        }
        $handler = self::getHandler();
        if ($handler->floatGenerationTime == null) {
            throw new LogicException("Generation Time not started.");
        }
        return $floatStopTime - $handler->floatGenerationTime;
    }
    
    /**
     * Start the page generation timer. Usually at the beginning of the page
     * rendering.
     *
     * @param float $floatStartTime The timer start time.
     * 
     * @return void
     */
    public static function setGenerationTime($floatStartTime = null)
    {
        if ($floatStartTime == null) {
            $floatStartTime = microtime(true);
        }
        $handler = self::getHandler();
        if ($handler->floatGenerationTime != null) {
            throw new LogicException("Generation Time already started.");
        }
        $handler->floatGenerationTime = $floatStartTime;
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
            throw new LogicException("Failed to load Smarty");
        }
        $libSmarty .= '/libs/Smarty.class.php';
        $baseSmarty = dirname(__FILE__) . '/../../SmartyTemplates/';
        include_once $libSmarty;
        $objSmarty = new Smarty();
        $objSmarty->setTemplateDir($baseSmarty);
        $objSmarty->setCompileDir(
            Container_Config::brokerByID('TemporaryFiles', '/tmp')->getKey('value') . '/smartyCompiled'
        );
        $objSmarty->left_delimiter = '<!--SM:';
        $objSmarty->right_delimiter = ':SM-->';
        if (is_array($arrAssignments) and count($arrAssignments) > 0) {
            foreach ($arrAssignments as $key=>$value) {
                $objSmarty->assign($key, $value);
            }
        }
        if (Container_Config::brokerByID('smarty_debug', 'false')->getKey('value') != 'false') {
            $objSmarty->debugging = true;
            if (file_exists($baseSmarty . $template . '.html.tpl')) {
                $objSmarty->display($template . '.html.tpl');
            } else {
                $objSmarty->display('Generic_Object.html.tpl');
            }
        } else {
            if (file_exists($baseSmarty . $template . '.html.tpl')) {
                self::sendHttpResponse(200, $objSmarty->fetch($template . '.html.tpl'));
            } else {
                self::sendHttpResponse(200, $objSmarty->fetch('Generic_Object.html.tpl'));
            }
        }
    }

    /**
     * This function returns either a localized string or a default string if
     * the localized value hasn't been created
     *
     * @param array  $arrStrings  The array of translated strings
     * @param string $strLanguage The default language to return (for unit
     * testing purposes)
     * 
     * @return string
     */
    public static function translate($arrStrings, $key, $strLanguage = null)
    {
        $objCache = Base_Cache::getHandler();
        if (isset($objCache->arrCache[$key])) {
            return $objCache->arrCache[$key];
        } else {
            $objRequest = Container_Request::getRequest();
            $arrLanguages = $objRequest->get_arrAcceptLangs();
            if (! is_array($arrLanguages)) {
                $arrLanguages = array();
            }
            if ($strLanguage != null) {
                $arrLanguages[$strLanguage] = 2;
            }
            sort($arrLanguages, SORT_NUMERIC);
        
            if (! is_array($arrStrings) || ! isset($arrStrings[$key])) {
                throw new InvalidArgumentException('Not a valid array of strings');
            } elseif (count($arrStrings[$key]) == 0) {
                throw new InvalidArgumentException('No translation strings provided');
            }
        
            // Try to use the preferred languages in order (dialect then base)
            foreach ($arrLanguages as $strLanguage => $intLanguageValue) {
                $intLanguageValue = null;
                if (isset($arrStrings[$strLanguage])) {
                    $objCache->arrCache[$key] = $arrStrings[$key][$strLanguage];
                    return $arrStrings[$key][$strLanguage];
                } elseif (isset($arrStrings[$key][substr($strLanguage, 0, 2)])) {
                    $objCache->arrCache[$key] = $arrStrings[$key][substr($strLanguage, 0, 2)];
                    return $arrStrings[$key][substr($strLanguage, 0, 2)];
                }
            }
        
            // If none of the preferred strings exist, use the english base as
            // default. If that also doesn't exist, thrown an exception.
            if (isset($arrStrings[$key]['en'])) {
                $objCache->arrCache[$key] = $arrStrings[$key]['en'];
                return $arrStrings[$key]['en'];
            } else {
                throw new InvalidArgumentException('No valid strings found');
            }
            // Derive string
            return $string;
        }
    }
}
