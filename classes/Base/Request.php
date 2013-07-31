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
 * This class reads everything related to the request that might be useful to the
 * script, and passes it back as an array.
 *
 * @category Base_Request
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Base_Request
{
    protected $arrMediaTypes = array(
        'application/json' => array(
            'media' => false, 'rest' => true, 'site' => false
        ),
        'application/atom+xml' => array(
            'media' => false, 'rest' => true, 'site' => false
        ),
        'application/pdf' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/postscript' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/rss+xml' => array(
            'media' => false, 'rest' => true, 'site' => false
        ),
        'application/soap+xml' => array(
            'media' => false, 'rest' => false, 'site' => false
        ),
        'application/xhtml+xml' => array(
            'media' => false, 'rest' => true, 'site' => true
        ),
        'application/zip' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/x-gzip' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'audio/mpeg' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'audio/mp4' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'audio/ogg' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'image/png' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'image/jpeg' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'image/gif' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'image/svg+xml' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'text/css' => array(
            'media' => true, 'rest' => false, 'site' => true
        ),
        'text/html' => array(
            'media' => false, 'rest' => true, 'site' => true
        ),
        'text/csv' => array(
            'media' => false, 'rest' => true, 'site' => false
        ),
        'text/xml' => array(
            'media' => false, 'rest' => true, 'site' => false
        ),
        'text/plain' => array(
            'media' => false, 'rest' => true, 'site' => true
        ),
        'text/vcard' => array(
            'media' => false, 'rest' => true, 'site' => true
        ),
        'video/ogg' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'video/mpeg' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'video/mp4' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'video/webm' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'video/x-ms-wmv' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/msword' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.oasis.opendocument.text' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.ms-excel' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.oasis.opendocument.spreadsheet' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.ms-powerpoint' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'application/vnd.oasis.opendocument.presentation' => array(
            'media' => true, 'rest' => false, 'site' => false
        ),
        'text/javascript' => array(
            'media' => true, 'rest' => false, 'site' => false
        )
    );

    protected $arrRequestUrl      = null;
    protected $requestUrlFull     = null;
    protected $requestUrlExParams = null;
    protected $strUsername        = null;
    protected $strPassword        = null;
    protected $strRequestMethod   = null;
    protected $hasIfModifiedSince = null;
    protected $hasIfNoneMatch     = null;
    protected $arrRqstParameters  = null;
    protected $strPathSite        = null;
    protected $strPathRouter      = null;
    protected $arrPathItems       = null;
    protected $strPathFormat      = null;
    protected $intPrefAcceptType  = 0;
    protected $strPrefAcceptType  = null;
    protected $arrAcceptTypes     = null;
    protected $intPrefAcceptLang  = 0;
    protected $strPrefAcceptLang  = null;
    protected $arrAcceptLangs     = null;
    protected $strBasePath        = null;
    protected $strUserAgent       = null;
    protected $arrSession         = null;
    protected $isParsed           = false;
    
    /**
     * This function reads the $arrMediaTypes array above, and returns whether 
     * it's a valid site, rest (api) or media type.
     * 
     * It is used when making decisions about whether to return data to the user 
     * in that format.
     *
     * @param string $category  The type of request we believe this media type 
     * should work for
     * @param string $mediaType The media type (replaced, on null with the 
     * detected media type)
     * 
     * @return boolean The value from the table above.
     */
    public function hasMediaType($category = 'site', $mediaType = null)
    {
        if ($mediaType == null) {
            $mediaType = $this->strPrefAcceptType;
        }
        if (isset($this->arrMediaTypes[$mediaType])) {
            switch ($category) {
            case 'media':
            case 'rest':
            case 'site':
                return $this->arrMediaTypes[$mediaType][$category];
                break;
            default:
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * This function parses the request values
     * 
     * @param array  $arrGlobals A dependency injection entry for $GLOBALS
     * @param array  $arrServer  A dependency injection entry for $_SERVER
     * @param array  $arrRequest A dependency injection entry for $_REQUEST
     * @param array  $arrGet     A dependency injection entry for $_GET
     * @param array  $arrPost    A dependency injection entry for $_POST
     * @param array  $arrFiles   A dependency injection entry for $_FILES
     * @param string $strInput   A dependency injection entry for php://input
     * @param array  $arrSession A dependency injection entry for $_SESSION
     *
     * @return Base_Request
     */
    public function parse(
        $arrGlobals = null, 
        $arrServer = null,
        $arrRequest = null,
        $arrGet = null,
        $arrPost = null,
        $arrFiles = null,
        $strInput = null,
        $arrSession = null
    ) {
        if ($this->isParsed) {
            return true;
        } else {
            $this->isParsed = true;
        }
        if ($arrGlobals == null) {
            $arrGlobals = $GLOBALS;
        }
        if ($arrServer == null) {
            $arrServer = $_SERVER;
        }
        if ($arrRequest == null) {
            $arrRequest = $_REQUEST;
        }
        if ($arrGet == null) {
            $arrGet = $_GET;
        }
        if ($arrPost == null) {
            $arrPost = $_POST;
        }
        if ($arrFiles == null) {
            $arrFiles = $_FILES;
        }
        if ($strInput == null) {
            $strInput = file_get_contents('php://input');
        }
        if ($arrSession == null && isset($_COOKIE['PHPSESSID'])) {
            Base_GeneralFunctions::startSession();
            $arrSession = &$_SESSION;
        }

        // First, get the script name or URL, and any parameters received

        if ( ! isset($arrServer['REQUEST_METHOD'])) {
            if (preg_match('/\/(.*)$/', $arrGlobals['argv'][0]) == 0) {
                $filename = trim(`pwd`) . '/' . $arrGlobals['argv'][0];
            } else {
                $filename = $arrGlobals['argv'][0];
            }
            $url = 'file://' . $filename;
            $args = $arrGlobals['argv'];
            unset($args[0]);
            $data = array();
            foreach ($args as $key => $part) {
                if (preg_match('/^([^=]+)=(.*)$/', $part, $matches)) {
                    $data[$matches[1]] = $matches[2];
                } else {
                    $data[$part] = '';
                }
            }
            $this->strRequestMethod = 'file';
        } else {
            $url = "http";
            if (isset($arrServer['HTTPS']) && $arrServer['HTTPS'] == '') {
                unset($arrServer['HTTPS']);
            }
            if (isset($arrServer['HTTPS'])) {
                $url .= 's';
            }
            $url .= '://';

            // Let's check if they gave us HTTP credentials

            if (isset($arrServer['HTTP_AUTHORIZATION'])) {
                $arrAuthParams = explode(":", base64_decode(substr($arrServer['HTTP_AUTHORIZATION'], 6)));
                $this->strUsername = $arrAuthParams[0];
                unset($arrAuthParams[0]);
                $this->strPassword = implode('', $arrAuthParams);
            } elseif (isset($arrServer['PHP_AUTH_USER']) and isset($arrServer['PHP_AUTH_PW'])) {
                $this->strUsername = $arrServer['PHP_AUTH_USER'];
                $this->strPassword = $arrServer['PHP_AUTH_PW'];
            }

            if ($this->strUsername != null) {
                $url .= $this->strUsername;
                if ($this->strPassword != null) {
                    $url .= ':' . $this->strPassword;
                }
                $url .= '@';
            }
            
            $url .= $arrServer['SERVER_NAME'];
            if ((isset($arrServer['HTTPS']) 
                && $arrServer['SERVER_PORT'] != 443) 
                || ( ! isset($arrServer['HTTPS']) 
                && $arrServer['SERVER_PORT'] != 80)
            ) {
                $url .= ':' . $arrServer['SERVER_PORT'];
            }
            $url .= $arrServer['REQUEST_URI'];
            
            switch(strtolower($arrServer['REQUEST_METHOD'])) {
            case 'head':
                // Typically a request to see if this has changed since the last time
                $this->strRequestMethod = 'head';
                $data = $arrRequest;
                break;
            case 'get':
                $this->strRequestMethod = 'get';
                $data = $arrGet;
                break;
            case 'post':
                $this->strRequestMethod = 'post';
                $data = $arrPost;
                if (isset($arrFiles) and is_array($arrFiles)) {
                    $data['_FILES'] = $arrFiles;
                }
                break;
            case 'put':
                $this->strRequestMethod = 'put';
                parse_str($strInput, $arrPut);
                $data = $arrPut;
                break;
            case 'delete':
                $this->strRequestMethod = 'delete';
                $data = $arrRequest;
                break;
            }
        }

        // Next, parse the URL or script name we just received, and store it.

        $this->arrRequestUrl = parse_url($url);
        $this->requestUrlFull = $url;

        // Take off any parameters, if they've been kept

        if (strlen(trim($this->requestUrlFull)) > 0) {
            $match = preg_match('/^([^\?]+)/', $this->requestUrlFull, $matches);
            $this->requestUrlExParams = $matches[1];
        }
        
        // Store any of the parameters we aquired before. Add an "if-modified-since" parameter too.

        if (isset($arrServer['HTTP_IF_MODIFIED_SINCE'])) {
            // Taken from http://www.justsoftwaresolutions.co.uk/webdesign ... 
            // /provide-last-modified-headers-and-handle-if-modified-since-in-php.html
            $this->hasIfModifiedSince = preg_replace('/;.*$/', '', $arrServer["HTTP_IF_MODIFIED_SINCE"]);
        }
        
        if (isset($arrServer['HTTP_IF_NONE_MATCH'])) {
            preg_match_all('/"([^"^,]+)/', $arrServer["HTTP_IF_NONE_MATCH"], $hasIfNoneMatch);
            if (isset($hasIfNoneMatch[0])) {
                unset($hasIfNoneMatch[0]);
                foreach ($hasIfNoneMatch as $tempIfNoneMatch) {
                    if (is_array($tempIfNoneMatch)) {
                        foreach ($tempIfNoneMatch as $value) {
                            $this->hasIfNoneMatch[] = $value;
                        }
                    }
                }
            }
        }
        
        // Make the list of accepted types into an array, and then step through it.
        if (isset($arrServer['HTTP_ACCEPT_LANGUAGE'])) {
            $arrAccept = explode(',', strtolower(str_replace(' ', '', $arrServer['HTTP_ACCEPT_LANGUAGE'])));
            foreach ($arrAccept as $acceptItem) {
                $q = 1;
                if (strpos($acceptItem, ';q=')) {
                    list($acceptItem, $q) = explode(';q=', $acceptItem);
                }
                if ($q > 0) {
                    $this->arrAcceptLangs[$acceptItem] = $q;
                    if ($q > $this->intPrefAcceptLang) {
                        $this->intPrefAcceptLang = $q;
                        $this->strPrefAcceptLang = $acceptItem;
                    }
                }
            }
        }

        $this->arrRqstParameters = $data;
        
        // Special case for browsers who can't cope with sending the full range
        // of HTTP actions.
        if (isset($this->arrRqstParameters['HTTPaction'])) {
            switch(strtolower($this->arrRqstParameters['HTTPaction'])) {
            case 'head':
                // Typically a request to see if this has changed since the last time
                $this->strRequestMethod = 'head';
                unset($this->arrRqstParameters['HTTPaction']);
                break;
            case 'delete':
                $this->strRequestMethod = 'delete';
                unset($this->arrRqstParameters['HTTPaction']);
                break;
            }
        }

        // Remove the trailing slash from the path, if there is one

        if (substr($this->arrRequestUrl['path'], -1) == '/') {
            $this->arrRequestUrl['path'] = substr($this->arrRequestUrl['path'], 0, -1);
        }

        // If the path is just / then keep it, otherwise remove the leading slash from the path

        $match = preg_match('/\/(.*)/', $this->arrRequestUrl['path'], $matches);
        if ($match > 0) {
            $this->arrRequestUrl['path'] = $matches[1];
        }

        // We need to find where the start of the site is (for example, 
        // it may be http://webserver/myproject, or http://myproject)

        // Assume the start is at the end of http://servername/ and that the 
        // router path is everything from there out.

        $this->strPathSite = '';
        $this->strPathRouter = $this->arrRequestUrl['path'];

        // Next make sure that we have a script name, and that this is not just a CLI script.

        if (isset($arrServer['REQUEST_METHOD']) && isset($arrServer['SCRIPT_NAME'])) {

            // Separate out the individual characters of the URL path we received and the script path

            $arrPathElements = str_split($this->arrRequestUrl['path']);
            $match = preg_match('/\/(.*)$/', $arrServer['SCRIPT_NAME'], $matches);
            $arrScriptElements = str_split($matches[1]);

            // Then compare each character one-by-one until we reach the end of 
            // the URL or the script name and path names diverge

            $char = 0;
            while (isset($arrPathElements[$char]) 
                && isset($arrScriptElements[$char]) 
                && $arrPathElements[$char] == $arrScriptElements[$char]
            ) {
                $char++;
            }

            // Use that information to build the pathSite (the base URL for the site) and the routed path (/my/action)

            $this->strPathSite = substr($this->arrRequestUrl['path'], 0, $char);
            $this->strPathRouter = substr($this->arrRequestUrl['path'], $char);
        }

        // To ensure the first character of the pathRouter isn't '/', check for it and trim it.
        // I can't actually figure out why this went in here, but I don't seem to be able to test it!
        
        if (substr($this->strPathRouter, 0, 1) == '/') {
            $this->strPathRouter = substr($this->strPathRouter, 1);
        }
        
        // And ensure the last character of the site path isn't '/', check for that and trim it.
        if (substr($this->strPathSite, -1) == '/') {
            $this->strPathSite = substr($this->strPathSite, 0, -1);
        }

        // Get the routed path as it's slash-delimited values into an array

        $this->arrPathItems = explode('/', $this->strPathRouter);

        // Let's talk about the format to return data as, or rather, the preferred (Internet Media) accepted-type
        // This was inserted after reading this comment:
        // http://www.lornajane.net/posts/2012/building-a-restful-php-server-understanding-the-request#comment-3218

        $this->strPathFormat = '';
        $this->intPrefAcceptType = 0;
        $this->strPrefAcceptType = 'text/html';
        $this->arrAcceptTypes = array();
        $arrDenyTypes = array();

        // This is based on http://stackoverflow.com/questions/1049401/how-to-select-content-type-from-http-accept-header-in-php

        // Make the list of accepted types into an array, and then step through it.
        if (isset($arrServer['HTTP_ACCEPT'])) {
            $arrAccept = explode(',', strtolower(str_replace(' ', '', $arrServer['HTTP_ACCEPT'])));
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
                    $this->arrAcceptTypes[$acceptItem] = $q;
                    if ($q > $this->intPrefAcceptType) {
                        $this->intPrefAcceptType = $q;
                        $this->strPrefAcceptType = $acceptItem;
                    }
                } else {
                    $arrDenyTypes[$acceptItem] = true;
                }
            }

            // If the last item contains a dot, for example file.json, then we can suspect the user is specifying the file format to prefer.
            // So, let's look at the last chunk of the requested URL. Does it contain a dot in it?

            $arrLastUrlItem = explode('.', $this->arrPathItems[count($this->arrPathItems)-1]);
            if (count($arrLastUrlItem) > 1) {

                // First we clear down the last path item, as we're going to be re-creating it without the format tag

                $this->arrPathItems[count($this->arrPathItems)-1] = '';

                // Next we step through each part of that last chunk, looking for the bit after the last dot.

                foreach ($arrLastUrlItem as $key=>$urlItem) {

                    // If it's the last part, this is the format we'll be using, otherwise rebuild that last item

                    if ($key + 1 == count($arrLastUrlItem)) {
                        $this->strPathFormat = $urlItem;

                        // Remove the pathFormat from the pathRouter, and the "."

                        $this->strPathRouter = substr($this->strPathRouter, 0, - (1 + strlen($this->strPathFormat)));

                        // Now let's try and mark the format up as something we can use as an accept type. Here are the common ones
                        // you're likely to see (from http://en.wikipedia.org/wiki/Internet_media_type)

                        switch (strtolower($this->strPathFormat)) {

                        // Application types

                        case 'json':
                            $this->setAcceptType(
                                'application/json',
                                $arrDenyTypes
                            );
                            break;
                        case 'atom':
                            $this->setAcceptType(
                                'application/atom+xml',
                                $arrDenyTypes
                            );
                            break;
                        case 'pdf':
                            $this->setAcceptType(
                                'application/pdf',
                                $arrDenyTypes
                            );
                            break;
                        case 'ps':
                            $this->setAcceptType(
                                'application/postscript',
                                $arrDenyTypes
                            );
                            break;
                        case 'rss':
                            $this->setAcceptType(
                                'application/rss+xml',
                                $arrDenyTypes
                            );
                            break;
                        case 'soap':
                            $this->setAcceptType(
                                'application/soap+xml',
                                $arrDenyTypes
                            );
                            break;
                        case 'xhtml':
                            $this->setAcceptType(
                                'application/xhtml+xml',
                                $arrDenyTypes
                            );
                            break;
                        case 'zip':
                            $this->setAcceptType(
                                'application/zip',
                                $arrDenyTypes
                            );
                            break;
                        case 'gz':
                        case 'gzip':
                            $this->setAcceptType(
                                'application/x-gzip',
                                $arrDenyTypes
                            );
                            break;

                        // Audio Types

                        case 'mp3':
                        case 'mpeg3':
                            $this->setAcceptType(
                                'audio/mpeg',
                                $arrDenyTypes
                            );
                            break;
                        case 'm4a':
                            $this->setAcceptType(
                                'audio/mp4',
                                $arrDenyTypes
                            );
                            break;
                        case 'ogg':
                            $this->setAcceptType(
                                'audio/ogg',
                                $arrDenyTypes
                            );
                            break;

                        // Image types

                        case 'png':
                            $this->setAcceptType(
                                'image/png',
                                $arrDenyTypes
                            );
                            break;
                        case 'jpg':
                        case 'jpeg':
                            $this->setAcceptType(
                                'image/jpeg',
                                $arrDenyTypes
                            );
                            break;
                        case 'gif':
                            $this->setAcceptType(
                                'image/gif',
                                $arrDenyTypes
                            );
                            break;
                        case 'svg':
                            $this->setAcceptType(
                                'image/svg+xml',
                                $arrDenyTypes
                            );
                            break;

                        // Text types

                        case 'css':
                            $this->setAcceptType(
                                'text/css',
                                $arrDenyTypes
                            );
                            break;
                        case 'htm':
                        case 'html':
                            $this->setAcceptType(
                                'text/html',
                                $arrDenyTypes
                            );
                            break;
                        case 'csv':
                            $this->setAcceptType(
                                'text/csv',
                                $arrDenyTypes
                            );
                            break;
                        case 'xml':
                            $this->setAcceptType(
                                'text/xml',
                                $arrDenyTypes
                            );
                            break;
                        case 'txt':
                            $this->setAcceptType(
                                'text/plain',
                                $arrDenyTypes
                            );
                            break;
                        case 'vcd':
                            $this->setAcceptType(
                                'text/vcard',
                                $arrDenyTypes
                            );
                            break;

                        // Video types

                        case 'ogv':
                            $this->setAcceptType(
                                'video/ogg',
                                $arrDenyTypes
                            );
                            break;
                        case 'avi':
                            $this->setAcceptType(
                                'video/mpeg',
                                $arrDenyTypes
                            );
                            break;
                        case 'mp4':
                        case 'mpeg':
                            $this->setAcceptType(
                                'video/mp4',
                                $arrDenyTypes
                            );
                            break;
                        case 'webm':
                            $this->setAcceptType(
                                'video/webm',
                                $arrDenyTypes
                            );
                            break;
                        case 'wmv':
                            $this->setAcceptType(
                                'video/x-ms-wmv',
                                $arrDenyTypes
                            );
                            break;

                        // Open/Libre/MS Office file formats

                        case 'doc':
                            $this->setAcceptType(
                                'application/msword',
                                $arrDenyTypes
                            );
                            break;
                        case 'docx':
                            $this->setAcceptType(
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                $arrDenyTypes
                            );
                            break;
                        case 'odt':
                            $this->setAcceptType(
                                'application/vnd.oasis.opendocument.text',
                                $arrDenyTypes
                            );
                            break;
                        case 'xls':
                            $this->setAcceptType(
                                'application/vnd.ms-excel',
                                $arrDenyTypes
                            );
                            break;
                        case 'xlsx':
                            $this->setAcceptType(
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                $arrDenyTypes
                            );
                            break;
                        case 'ods':
                            $this->setAcceptType(
                                'application/vnd.oasis.opendocument.spreadsheet',
                                $arrDenyTypes
                            );
                            break;
                        case 'ppt':
                            $this->setAcceptType(
                                'application/vnd.ms-powerpoint',
                                $arrDenyTypes
                            );
                            break;
                        case 'pptx':
                            $this->setAcceptType(
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                $arrDenyTypes
                            );
                            break;
                        case 'odp':
                            $this->setAcceptType(
                                'application/vnd.oasis.opendocument.presentation',
                                $arrDenyTypes
                            );
                            break;
                        case 'js':
                            $this->setAcceptType(
                                'text/javascript',
                                $arrDenyTypes
                            );
                            break;

                        // Not one of the above types. Hopefully you won't see this!!!

                        default:
                            $this->setAcceptType(
                                'unknown/' . $this->strPathFormat,
                                $arrDenyTypes
                            );
                        }
                    } else {
                        if ($this->arrPathItems[count($this->arrPathItems)-1] != '') {
                            $this->arrPathItems[count($this->arrPathItems)-1] .= '.';
                        }
                        $this->arrPathItems[count($this->arrPathItems)-1] .= $urlItem;
                    }
                }
            }
        }

        // Next let's build the "basePath" - this is the URL which refers to base of the script and is used in the HTML to point back to
        // resources within this service.

        $this->strBasePath = $this->arrRequestUrl['scheme'] . "://";
        if (isset($this->arrRequestUrl['host'])) {
            $this->strBasePath .= $this->arrRequestUrl['host'];
        }
        if (isset($this->arrRequestUrl['port']) and $this->arrRequestUrl['port'] != '') {
            $this->strBasePath .= ':' . $this->arrRequestUrl['port'];
        }
        if (isset($this->strPathSite) and $this->strPathSite != '') {
            $this->strBasePath .= '/' . $this->strPathSite;
        }
        $this->strBasePath .=  '/';

        // Let's get the user agent - it's just for a giggle in most cases, as it's not authorititive, but it might help if you're
        // getting site stats, or trying not to track people with cookies.

        if (isset($arrServer['HTTP_USER_AGENT'])) {
            // Remember, this isn't guaranteed to be accurate
            $this->strUserAgent = $arrServer['HTTP_USER_AGENT'];
        }
        
        // Add the Session data to the collected data
        $this->arrSession = $arrSession;
        
        return $this;
    }

    /**
     * This function updates the arrRequestData array with the MIME type to handle, based on the file extension.
     *
     * @param string $strAcceptType The MIME type
     * @param array  $arrDenyTypes  An array of mime types we're not interested in.
     * 
     * @return void
     */
    function setAcceptType(
        $strAcceptType = '', 
        $arrDenyTypes = array()
    ) {
        if (! isset($arrDenyTypes[$strAcceptType])) {
            $this->arrAcceptTypes[$strAcceptType] = 2;
        }
        if (2 > $this->intPrefAcceptType) {
            $this->intPrefAcceptType = 2;
            $this->strPrefAcceptType = $strAcceptType;
        }
        return $this->intPrefAcceptType;
    }
    
    /**
     * Return the exploded Request URL array
     *
     * @return array 
     */
    public function get_arrRequestUrl()
    {
        return $this->arrRequestUrl;
    }
    
    /**
     * Return the full Request URL
     *
     * @return string
     */
    public function get_requestUrlFull()
    {
        return $this->requestUrlFull;
    }
    
    /**
     * Return the full Request URL excluding GET parameters
     *
     * @return string 
     */
    public function get_requestUrlExParams()
    {
        return $this->requestUrlExParams;
    }
    
    /**
     * Return the Username from the request
     *
     * @return string 
     */
    public function get_strUsername()
    {
        return $this->strUsername;
    }

    /**
     * Return the Password from the request
     *
     * @return string 
     */
    public function get_strPassword()
    {
        return $this->strPassword;
    }

    /**
     * Return the request method (PUT, GET, POST, DELETE, HEAD, etc) from the 
     * request
     *
     * @return string 
     */
    public function get_strRequestMethod()
    {
        return $this->strRequestMethod;
    }
    
    /**
     * If set, return the "Has-If-Modified-Since" value
     *
     * @return null|datetime 
     */
    public function get_hasIfModifiedSince()
    {
        return $this->hasIfModifiedSince;
    }
    
    /**
     * If set, return the "If-None-Match" value (for etags associated to a page)
     *
     * @return null|string 
     */
    public function get_hasIfNoneMatch()
    {
        return $this->hasIfNoneMatch;
    }
    
    /**
     * Return the array of all the parameters supplied by the request.
     *
     * @return array 
     */
    public function get_arrRqstParameters()
    {
        return $this->arrRqstParameters;
    }
    
    /**
     * Return the path of everything in the URL past the router
     *
     * @return string
     */
    public function get_strPathSite()
    {
        return $this->strPathSite;
    }
    
    /**
     * Return the path of everything up to the Router.
     *
     * @return string
     */
    public function get_strPathRouter()
    {
        return $this->strPathRouter;
    }
    
    /**
     * Return the array of "path items" - basically, everything after the router
     * comes into play.
     *
     * @return array 
     */
    public function get_arrPathItems()
    {
        return $this->arrPathItems;
    }
    
    /**
     * If we've forced the Internet Type by providing a file extension, return
     * that value.
     *
     * @return string 
     */
    public function get_strPathFormat()
    {
        return $this->strPathFormat;
    }
    
    /**
     * Return the preferred (highest valued) accepted Internet Type (Mime Type),
     * where, if supplied, your browser can force it's preferred Internet Type
     * by supplying a known file extension.
     *
     * @return string 
     */
    public function get_strPrefAcceptType()
    {
        return $this->strPrefAcceptType;
    }
    
    /**
     * Return the array of Internet Types (Mime Types) your browser will accept, 
     * or, where forced by supplying a known file extension, that value as the top response.
     *
     * @return array 
     */
    public function get_arrAcceptTypes()
    {
        return $this->arrAcceptTypes;
    }
    
    /**
     * Return the base path of the URL, up to the point where the router takes
     * over.
     *
     * @return string
     */
    public function get_strBasePath()
    {
        return $this->strBasePath;
    }
    
    /**
     * Return the user agent string
     *
     * @return string
     */
    public function get_strUserAgent()
    {
        return $this->strUserAgent;
    }
    
    /**
     * Return the content of the $_SESSION array.
     *
     * @return array 
     */
    public function get_arrSession()
    {
        return $this->arrSession;
    }
    
    /**
     * Return the highest rated, first listed accepted language
     *
     * @return string 
     */
    public function get_strPrefAcceptLang()
    {
        return $this->strPrefAcceptLang;
    }

    /**
     * Return the array of accepted languages
     *
     * @return array
     */
    public function get_arrAcceptLangs()
    {
        return $this->arrAcceptLangs;
    }
}
