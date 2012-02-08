<?php

class base_request
{
    protected static $handler = null;
    protected $arrRequestData = null;

    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    protected static function getHandler()
    {
        if (self::$handler == null) {
            self::$handler = new self();
        }
        return self::$handler;
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
                case 'get':
                    $data = $_GET;
                    break;
                case 'post':
                    $data = $_POST;
                    if (isset($_FILES) and is_array($_FILES)) {
                        $data['_FILES'] = $_FILES;
                    }
                    break;
                case 'put':
                    parse_str(file_get_contents('php://input'), $_PUT);
                    $data = $_PUT;
                    break;
                case 'delete':
                case 'head':
                    $data = $_REQUEST;
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

        // Store any of the parameters we aquired before

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
        $handler->arrRequestData['arrAcceptTypes'] = array();

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
                        $handler->arrRequestData['arrAcceptTypes']['application/json'] = 1;
                        break;
                    case 'atom':
                        $handler->arrRequestData['arrAcceptTypes']['application/atom+xml'] = 1;
                        break;
                    case 'pdf':
                        $handler->arrRequestData['arrAcceptTypes']['application/pdf'] = 1;
                        break;
                    case 'ps':
                        $handler->arrRequestData['arrAcceptTypes']['application/postscript'] = 1;
                        break;
                    case 'rss':
                        $handler->arrRequestData['arrAcceptTypes']['application/rss+xml'] = 1;
                        break;
                    case 'soap':
                        $handler->arrRequestData['arrAcceptTypes']['application/soap+xml'] = 1;
                        break;
                    case 'xhtml':
                        $handler->arrRequestData['arrAcceptTypes']['application/xhtml+xml'] = 1;
                        break;
                    case 'zip':
                        $handler->arrRequestData['arrAcceptTypes']['application/zip'] = 1;
                        break;
                    case 'gz':
                    case 'gzip':
                        $handler->arrRequestData['arrAcceptTypes']['application/x-gzip'] = 1;
                        break;

                    // Audio Types

                    case 'mp3':
                    case 'mpeg3':
                        $handler->arrRequestData['arrAcceptTypes']['audio/mpeg'] = 1;
                        break;
                    case 'm4a':
                        $handler->arrRequestData['arrAcceptTypes']['audio/mp4'] = 1;
                        break;
                    case 'ogg':
                        $handler->arrRequestData['arrAcceptTypes']['audio/ogg'] = 1;
                        break;

                    // Image types

                    case 'png':
                        $handler->arrRequestData['arrAcceptTypes']['image/png'] = 1;
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $handler->arrRequestData['arrAcceptTypes']['image/jpeg'] = 1;
                        break;
                    case 'gif':
                        $handler->arrRequestData['arrAcceptTypes']['image/gif'] = 1;
                        break;
                    case 'svg':
                        $handler->arrRequestData['arrAcceptTypes']['image/svg+xml'] = 1;
                        break;

                    // Text types

                    case 'css':
                        $handler->arrRequestData['arrAcceptTypes']['text/css'] = 1;
                        break;
                    case 'htm':
                    case 'html':
                        $handler->arrRequestData['arrAcceptTypes']['text/html'] = 1;
                        break;
                    case 'csv':
                        $handler->arrRequestData['arrAcceptTypes']['text/csv'] = 1;
                        break;
                    case 'xml':
                        $handler->arrRequestData['arrAcceptTypes']['text/xml'] = 1;
                        break;
                    case 'txt':
                        $handler->arrRequestData['arrAcceptTypes']['text/plain'] = 1;
                        break;
                    case 'vcd':
                        $handler->arrRequestData['arrAcceptTypes']['text/vcard'] = 1;
                        break;

                    // Video types

                    case 'ogv':
                        $handler->arrRequestData['arrAcceptTypes']['video/ogg'] = 1;
                        break;
                    case 'avi':
                        $handler->arrRequestData['arrAcceptTypes']['video/mpeg'] = 1;
                        break;
                    case 'mp4':
                    case 'mpeg':
                        $handler->arrRequestData['arrAcceptTypes']['video/mp4'] = 1;
                        break;
                    case 'webm':
                        $handler->arrRequestData['arrAcceptTypes']['video/webm'] = 1;
                        break;
                    case 'wmv':
                        $handler->arrRequestData['arrAcceptTypes']['video/x-ms-wmv'] = 1;
                        break;

                    // Open/Libre/MS Office file formats

                    case 'doc':
                        $handler->arrRequestData['arrAcceptTypes']['application/msword'] = 1;
                        break;
                    case 'docx':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = 1;
                        break;
                    case 'odt':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.oasis.opendocument.text'] = 1;
                        break;
                    case 'xls':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.ms-excel'] = 1;
                        break;
                    case 'xlsx':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = 1;
                        break;
                    case 'ods':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.oasis.opendocument.spreadsheet'] = 1;
                        break;
                    case 'ppt':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.ms-powerpoint'] = 1;
                        break;
                    case 'pptx':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = 1;
                        break;
                    case 'odp':
                        $handler->arrRequestData['arrAcceptTypes']['application/vnd.oasis.opendocument.presentation'] = 1;
                        break;

                    // Not one of the above types. Hopefully you won't see this!!!

                    default:
                        $handler->arrRequestData['arrAcceptTypes']['unknown/' . $handler->arrRequestData['pathFormat']] = 1;
                    }
                } else {
                    if ($handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] != '') {
                        $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] .= '.';
                    }
                    $handler->arrRequestData['pathItems'][count($handler->arrRequestData['pathItems'])-1] .= $UrlItem;
                }
            }
        }
        
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

            // If the quality is 0, it's not accepted - in this case, we'll remove it!
            // Also, IE has a bad habit of saying it accepts everything. Ignore that case.

            if ($q > 0 && $acceptItem != '*/*') {
                $handler->arrRequestData['arrAcceptTypes'][$acceptItem] = $q;
            } else {
                if (isset($handler->arrRequestData['arrAcceptTypes'][$acceptItem])) {
                    unset($handler->arrRequestData['arrAcceptTypes'][$acceptItem]);
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
}