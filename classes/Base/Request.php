<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category CampFireManager2
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class reads everything related to the request that might be useful to the
 * script, and passes it back as an array.
 *
 * @category Base
 * @package  RequestParser
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_Request
{
    protected static $request_handler = null;
    protected $arrRequestData = null;
    protected static $arrMediaTypes = array(
        'application/json' => array('media' => false, 'rest' => true, 'site' => false),
        'application/atom+xml' => array('media' => false, 'rest' => true, 'site' => false),
        'application/pdf' => array('media' => true, 'rest' => false, 'site' => false),
        'application/postscript' => array('media' => true, 'rest' => false, 'site' => false),
        'application/rss+xml' => array('media' => false, 'rest' => true, 'site' => false),
        'application/soap+xml' => array('media' => false, 'rest' => false, 'site' => false),
        'application/xhtml+xml' => array('media' => false, 'rest' => true, 'site' => true),
        'application/zip' => array('media' => true, 'rest' => false, 'site' => false),
        'application/x-gzip' => array('media' => true, 'rest' => false, 'site' => false),
        'audio/mpeg' => array('media' => true, 'rest' => false, 'site' => false),
        'audio/mp4' => array('media' => true, 'rest' => false, 'site' => false),
        'audio/ogg' => array('media' => true, 'rest' => false, 'site' => false),
        'image/png' => array('media' => true, 'rest' => false, 'site' => false),
        'image/jpeg' => array('media' => true, 'rest' => false, 'site' => false),
        'image/gif' => array('media' => true, 'rest' => false, 'site' => false),
        'image/svg+xml' => array('media' => true, 'rest' => false, 'site' => false),
        'text/css' => array('media' => true, 'rest' => false, 'site' => true),
        'text/html' => array('media' => false, 'rest' => true, 'site' => true),
        'text/csv' => array('media' => false, 'rest' => true, 'site' => false),
        'text/xml' => array('media' => false, 'rest' => true, 'site' => false),
        'text/plain' => array('media' => false, 'rest' => true, 'site' => true),
        'text/vcard' => array('media' => false, 'rest' => true, 'site' => true),
        'video/ogg' => array('media' => true, 'rest' => false, 'site' => false),
        'video/mpeg' => array('media' => true, 'rest' => false, 'site' => false),
        'video/mp4' => array('media' => true, 'rest' => false, 'site' => false),
        'video/webm' => array('media' => true, 'rest' => false, 'site' => false),
        'video/x-ms-wmv' => array('media' => true, 'rest' => false, 'site' => false),
        'application/msword' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.oasis.opendocument.text' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.ms-excel' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.oasis.opendocument.spreadsheet' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.ms-powerpoint' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('media' => true, 'rest' => false, 'site' => false),
        'application/vnd.oasis.opendocument.presentation' => array('media' => true, 'rest' => false, 'site' => false)
    );

    /**
     * This function reads the $arrMediaTypes array above, and returns whether it's a valid site, rest (api) or media type.
     * 
     * It is used when making decisions about whether to return data to the user in that format.
     *
     * @param string $category  The type of request we believe this media type should work for
     * @param string $mediaType The media type (replaced, on null with the detected media type)
     * 
     * @return boolean The value from the table above.
     */
    public static function getMediaType($category = 'site', $mediaType = null)
    {
        if ($mediaType == null) {
            $handler = self::getHandler();
            $mediaType = $handler->arrRequestData['strPreferredAcceptType'];
        }
        if (isset(Base_Request::$arrMediaTypes[$mediaType])) {
            switch ($category) {
            case 'media':
            case 'rest':
            case 'site':
                return Base_Request::$arrMediaTypes[$mediaType][$category];
                break;
            }
        }
        return false;
    }
    
    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    protected static function getHandler()
    {
        if (self::$request_handler == null) {
            self::$request_handler = new self();
        }
        return self::$request_handler;
    }

    /**
     * This function returns a parsed version of the data used to request this page
     *
     * @return array The compiled data
     */
    public function getRequest()
    {
        // If we've parsed the Request Parameters before, just return that data

        $handler = self::getHandler();
        if ($handler->arrRequestData != null) {
            return $handler->arrRequestData;
        }

        // First, get the script name or URL, and any parameters received

        if ( ! isset($_SERVER['REQUEST_METHOD'])) {
            if (preg_match('/\/(.*)$/', $GLOBALS['argv'][0]) == 0) {
                $filename = trim(`pwd`) . '/' . $GLOBALS['argv'][0];
            } else {
                $filename = $GLOBALS['argv'][0];
            }
            $url = 'file://' . $filename;
            $data = $GLOBALS['argv'];
            unset($data[0]);
        } else {
            $url = "http";
            if (isset($_SERVER['HTTPS'])) {
                $url .= 's';
            }
            $url .= '://';

            // Let's check if they gave us HTTP credentials

            $handler->arrRequestData['username'] = null;
            $handler->arrRequestData['password'] = null;
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth_params = explode(":", base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
                $handler->arrRequestData['username'] = $auth_params[0];
                unset($auth_params[0]);
                $handler->arrRequestData['password'] = implode('', $auth_params);
            } elseif (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
                $handler->arrRequestData['username'] = $_SERVER['PHP_AUTH_USER'];
                $handler->arrRequestData['password'] = $_SERVER['PHP_AUTH_PW'];
            }
           
            if ($handler->arrRequestData['username'] != null) {
                $url .= $handler->arrRequestData['username'];
                if ($handler->arrRequestData['password'] != null) {
                    $url .= ':' . $handler->arrRequestData['password'];
                }
                $url .= '@';
            }
            $url .= $_SERVER['SERVER_NAME'];
            if ((isset($_SERVER['HTTPS']) and $_SERVER['SERVER_PORT'] != 443) || ( ! isset($_SERVER['HTTPS']) and $_SERVER['SERVER_PORT'] != 80)) {
                $url .= ':' . $_SERVER['SERVER_PORT'];
            }
            $url .= $_SERVER['REQUEST_URI'];
            switch(strtolower($_SERVER['REQUEST_METHOD'])) {
            case 'head':
                // Typically a request to see if this has changed since the last time
                $handler->arrRequestData['method'] = 'head';
                $data = $_REQUEST;
                break;
            case 'get':
                $data = $_GET;
                break;
            case 'post':
                $handler->arrRequestData['method'] = 'post';
                $data = $_POST;
                if (isset($_FILES) and is_array($_FILES)) {
                    $data['_FILES'] = $_FILES;
                }
                break;
            case 'put':
                $handler->arrRequestData['method'] = 'put';
                parse_str(file_get_contents('php://input'), $_PUT);
                $data = $_PUT;
                break;
            case 'delete':
                $handler->arrRequestData['method'] = 'delete';
                $data = $_REQUEST;
                break;
            }
        }

        // Next, parse the URL or script name we just received, and store it.

        $handler->arrRequestData = parse_url($url);
        $handler->arrRequestData['requestUrlFull'] = $url;

        // Take off any parameters, if they've been kept

        $match = preg_match('/^([^\?]+)/', $handler->arrRequestData['requestUrlFull'], $matches);
        if ($match > 0) {
            $handler->arrRequestData['requestUrlExcludingParameters'] = $matches[1];
        } else {
            $handler->arrRequestData['requestUrlExcludingParameters'] = $url;
        }

        // Store any of the parameters we aquired before. Add an "if-modified-since" parameter too.

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // Taken from http://www.justsoftwaresolutions.co.uk/webdesign/provide-last-modified-headers-and-handle-if-modified-since-in-php.html
            $data['If-Modified-Since'] = preg_replace('/;.*$/','',$_SERVER["HTTP_IF_MODIFIED_SINCE"]);
        }

        $handler->arrRequestData['requestUrlParameters'] = $data;

        // Remove the trailing slash from the path, if there is one

        if (substr($handler->arrRequestData['path'], -1) == '/') {
            $handler->arrRequestData['path'] = substr($handler->arrRequestData['path'], 0, -1);
        }

        // If the path is just / then keep it, otherwise remove the leading slash from the path

        $match = preg_match('/\/(.*)/', $handler->arrRequestData['path'], $matches);
        if ($match > 0) {
            $handler->arrRequestData['path'] = $matches[1];
        }

        // We need to find where the start of the site is (for example, it may be http://webserver/myproject, or http://myproject)

        // Assume the start is at the end of http://servername/ and that the router path is everything from there out.

        $handler->arrRequestData['pathSite'] = '';
        $handler->arrRequestData['pathRouter'] = $handler->arrRequestData['path'];

        // Next make sure that we have a script name, and that this is not just a CLI script.

        if (isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['SCRIPT_NAME'])) {

            // Separate out the individual characters of the URL path we received and the script path

            $path_elements = str_split($handler->arrRequestData['path']);
            $match = preg_match('/\/(.*)$/', $_SERVER['SCRIPT_NAME'], $matches);
            $script_elements = str_split($matches[1]);

            // Then compare each character one-by-one until we reach the end of the URL or the script name and path names diverge

            $char = 0;
            while (isset($path_elements[$char]) && isset($script_elements[$char]) && $path_elements[$char] == $script_elements[$char]) {
                $char++;
            }

            // Use that information to build the pathSite (the base URL for the site) and the routed path (/my/action)

            $handler->arrRequestData['pathSite'] = substr($handler->arrRequestData['path'], 0, $char);
            $handler->arrRequestData['pathRouter'] = substr($handler->arrRequestData['path'], $char);
        }

        // To ensure the first character of the pathRouter isn't '/', check for it and trim it.
        
        if (substr($handler->arrRequestData['pathRouter'], 0, 1) == '/') {
            $handler->arrRequestData['pathRouter'] = substr($handler->arrRequestData['pathRouter'], 1);
        }

        // Get the routed path as it's slash-delimited values into an array

        $handler->arrRequestData['pathItems'] = explode('/', $handler->arrRequestData['pathRouter']);

        // Let's talk about the format to return data as, or rather, the preferred (Internet Media) accepted-type
        // This was inserted after reading this comment:
        // http://www.lornajane.net/posts/2012/building-a-restful-php-server-understanding-the-request#comment-3218

        $handler->arrRequestData['pathFormat'] = '';
        $handler->arrRequestData['intPreferredAcceptType'] = 0;
        $handler->arrRequestData['strPreferredAcceptType'] = 'text/html';
        $handler->arrRequestData['arrAcceptTypes'] = array();
        $handler->arrRequestData['arrDenyTypes'] = array();

        // This is based on http://stackoverflow.com/questions/1049401/how-to-select-content-type-from-http-accept-header-in-php

        // Make the list of accepted types into an array, and then step through it.

        $arrAccept = explode(',', strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT'])));
        foreach ($arrAccept as $acceptItem) {

            // All accepted Internet Media Types (or Mime Types, as they once we known) have a Q (Quality?) value
            // The default "Q" value is 1;
            $q = 1;

            // but the client may have sent another value
            if (strpos($acceptItem, ';q=')) {
                // In which case, use it.
                list($acceptItem, $q) = explode(';q=', $acceptItem);
            }

            // If the quality is 0, it's not accepted - in this case, so why bother logging it?
            // Also, IE has a bad habit of saying it accepts everything. Ignore that case.

            if ($q > 0 && $acceptItem != '*/*') {
                $handler->arrRequestData['arrAcceptTypes'][$acceptItem] = $q;
                if ($q > $handler->arrRequestData['intPreferredAcceptType']) {
                    $handler->arrRequestData['intPreferredAcceptType'] = $q;
                    $handler->arrRequestData['strPreferredAcceptType'] = $acceptItem;
                }
            } else {
                $handler->arrRequestData['arrDenyTypes'][$acceptItem] = true;
            }
        }
        
        // If the last item contains a dot, for example file.json, then we can suspect the user is specifying the file format to prefer.
        // So, let's look at the last chunk of the requested URL. Does it contain a dot in it?

        $arrLastUrlItem = explode('.', $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1]);
        if (count($arrLastUrlItem) > 1) {

            // First we clear down the last path item, as we're going to be re-creating it without the format tag

            $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] = '';

            // Next we step through each part of that last chunk, looking for the bit after the last dot.

            foreach ($arrLastUrlItem as $key=>$UrlItem) {

                // If it's the last part, this is the format we'll be using, otherwise rebuild that last item

                if ($key + 1 == count($arrLastUrlItem)) {
                    $handler->arrRequestData['pathFormat'] = $UrlItem;

                    // Remove the pathFormat from the pathRouter, and the "."

                    $handler->arrRequestData['pathRouter'] = substr($handler->arrRequestData['pathRouter'], 0, - (1 + strlen($handler->arrRequestData['pathFormat'])));

                    // Now let's try and mark the format up as something we can use as an accept type. Here are the common ones
                    // you're likely to see (from http://en.wikipedia.org/wiki/Internet_media_type)

                    switch (strtolower($handler->arrRequestData['pathFormat'])) {

                    // Application types

                    case 'json':
                        $handler->setAcceptType('application/json');
                        break;
                    case 'atom':
                        $handler->setAcceptType('application/atom+xml');
                        break;
                    case 'pdf':
                        $handler->setAcceptType('application/pdf');
                        break;
                    case 'ps':
                        $handler->setAcceptType('application/postscript');
                        break;
                    case 'rss':
                        $handler->setAcceptType('application/rss+xml');
                        break;
                    case 'soap':
                        $handler->setAcceptType('application/soap+xml');
                        break;
                    case 'xhtml':
                        $handler->setAcceptType('application/xhtml+xml');
                        break;
                    case 'zip':
                        $handler->setAcceptType('application/zip');
                        break;
                    case 'gz':
                    case 'gzip':
                        $handler->setAcceptType('application/x-gzip');
                        break;

                    // Audio Types

                    case 'mp3':
                    case 'mpeg3':
                        $handler->setAcceptType('audio/mpeg');
                        break;
                    case 'm4a':
                        $handler->setAcceptType('audio/mp4');
                        break;
                    case 'ogg':
                        $handler->setAcceptType('audio/ogg');
                        break;

                    // Image types

                    case 'png':
                        $handler->setAcceptType('image/png');
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $handler->setAcceptType('image/jpeg');
                        break;
                    case 'gif':
                        $handler->setAcceptType('image/gif');
                        break;
                    case 'svg':
                        $handler->setAcceptType('image/svg+xml');
                        break;

                    // Text types

                    case 'css':
                        $handler->setAcceptType('text/css');
                        break;
                    case 'htm':
                    case 'html':
                        $handler->setAcceptType('text/html');
                        break;
                    case 'csv':
                        $handler->setAcceptType('text/csv');
                        break;
                    case 'xml':
                        $handler->setAcceptType('text/xml');
                        break;
                    case 'txt':
                        $handler->setAcceptType('text/plain');
                        break;
                    case 'vcd':
                        $handler->setAcceptType('text/vcard');
                        break;

                    // Video types

                    case 'ogv':
                        $handler->setAcceptType('video/ogg');
                        break;
                    case 'avi':
                        $handler->setAcceptType('video/mpeg');
                        break;
                    case 'mp4':
                    case 'mpeg':
                        $handler->setAcceptType('video/mp4');
                        break;
                    case 'webm':
                        $handler->setAcceptType('video/webm');
                        break;
                    case 'wmv':
                        $handler->setAcceptType('video/x-ms-wmv');
                        break;

                    // Open/Libre/MS Office file formats

                    case 'doc':
                        $handler->setAcceptType('application/msword');
                        break;
                    case 'docx':
                        $handler->setAcceptType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                        break;
                    case 'odt':
                        $handler->setAcceptType('application/vnd.oasis.opendocument.text');
                        break;
                    case 'xls':
                        $handler->setAcceptType('application/vnd.ms-excel');
                        break;
                    case 'xlsx':
                        $handler->setAcceptType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        break;
                    case 'ods':
                        $handler->setAcceptType('application/vnd.oasis.opendocument.spreadsheet');
                        break;
                    case 'ppt':
                        $handler->setAcceptType('application/vnd.ms-powerpoint');
                        break;
                    case 'pptx':
                        $handler->setAcceptType('application/vnd.openxmlformats-officedocument.presentationml.presentation');
                        break;
                    case 'odp':
                        $handler->setAcceptType('application/vnd.oasis.opendocument.presentation');
                        break;

                    // Not one of the above types. Hopefully you won't see this!!!

                    default:
                        $handler->setAcceptType('unknown/' . $handler->arrRequestData['pathFormat']);
                    }
                } else {
                    if ($handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] != '') {
                        $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] .= '.';
                    }
                    $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] .= $UrlItem;
                }
            }
        }

        // Next let's build the "basePath" - this is the URL which refers to base of the script and is used in the HTML to point back to
        // resources within this service.

        $handler->arrRequestData['basePath'] = "{$handler->arrRequestData['scheme']}://{$handler->arrRequestData['host']}";
        if (isset($handler->arrRequestData['port']) and $handler->arrRequestData['port'] != '') {
            $handler->arrRequestData['basePath'] .= ':' . $handler->arrRequestData['port'];
        }
        if (isset($handler->arrRequestData['site_path']) and $handler->arrRequestData['site_path'] != '') {
            $handler->arrRequestData['basePath'] .= '/' . $handler->arrRequestData['site_path'];
        }
        $handler->arrRequestData['basePath'] .=  '/';

        // Let's get the user agent - it's just for a giggle in most cases, as it's not authorititive, but it might help if you're
        // getting site stats, or trying not to track people with cookies.

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            // Remember, this isn't guaranteed to be accurate
            $handler->arrRequestData['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        return $handler->arrRequestData;
    }

    /**
     * This function updates the arrRequestData array with the MIME type to handle, based on the file extension.
     *
     * @param string $strAcceptType The MIME type
     * 
     * @return void
     */
    function setAcceptType($strAcceptType = '')
    {
        if (! isset($this->arrRequestData['arrDenyTypes'][$strAcceptType])) {
            $this->arrRequestData['arrAcceptTypes'][$strAcceptType] = 2;
        }
        if (2 > $this->arrRequestData['intPreferredAcceptType']) {
            $this->arrRequestData['intPreferredAcceptType'] = 2;
            $this->arrRequestData['strPreferredAcceptType'] = $strAcceptType;
        }
    }
}