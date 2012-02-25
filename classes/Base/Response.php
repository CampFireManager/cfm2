<?php

class Base_Response
{
    protected static $http_status_codes = Array(
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

    /**
    * A helper function to ensure pages that require authentication, get them.
    *
    * @return void
    */
    function requireAuth()
    {
        $arrRequestDetails = Base_Request::getRequest();
        if ($arrRequestDetails['username'] == null) {
            static::sendHttpResponse(401);
        }
    }

    /**
     * Send a correctly formatted HTTP response to a request
     *
     * @param integer $status       HTTP response code
     * @param string  $body         Message to be sent
     * @param string  $content_type MIME type to send
     * @param string  $extra        Additional information beyond the routine HTTP status message
     *
     * @return void
     */
    function sendHttpResponse($status = 200, $body = null, $content_type = 'text/html', $extra = '')
    {
        // Send the relevant headers for this type of response
        header('HTTP/1.1 ' . $status . ' ' . static::$http_status_codes[$status]);
        header('Content-type: ' . $content_type);

        // Is there something for us to send
        if ($body != '' and $body != null) {
            // Send it
            echo $body;
            exit(0);
        } elseif ($content_type != 'text/html') {
            // We can't send anything because we don't have a valid response which is non-html based.
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
                list($uri, $data) = static::getPath();
                $message = 'The requested URL ' . $uri . ' was not found.';
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
                $message_content = "<p>{$message}</p>";
                // Add extra padding if required
                if ($extra != '') {
                    $message_content .= "\r\n    <p>$extra</p>";
                }
                // Here's the actual content to send.
                $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
' .                     '<html>
' .                     '  <head>
' .                     '    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
' .                     '    <title>' . $status . ' ' . static::$http_status_codes[$status] . '</title>
' .                     '  </head>
' .                     '  <body>
' .                     '    <h1>' . static::$http_status_codes[$status] . '</h1>
' .                     '    ' . $message_content . '
' .                     '  </body>
' .                     '</html>';
                echo $body;
            }
            exit(0);
        }
    }

    /**
     * Send an extended, yet still correctly formatted HTTP response to a request
     *
     * @param integer $status HTTP response code
     * @param string  $extra  Additional information beyond the routine HTTP status message
     *
     * @return void
     */
    function sendHttpResponseNote($status = 200, $extra = '')
    {
        sendHttpResponse($status, null, 'text/html', $extra);
    }

    /**
     * Return the string associated to an HTTP status code or false if it's wrong
     *
     * @param integer $status HTTP status code
     *
     * @return string|false The string associated to this code, or false if the code doesn't exist.
     */
    function returnHttpResponseString($status = 200)
    {
        if (isset(static::$http_status_codes[$status])) {
            return static::$http_status_codes[$status];
        } else {
            return false;
        }
    }

    /**
     * Provide a downloadable file and exit the script
     *
     * @param string  $file       File to send
     * @param boolean $is_resume  Can we supply headers to make this file resumable?
     * @param string  $media_type The Internet Media Type for this media. If unset, force the download in browsers.
     *
     * @return void
     *
     * @link http://www.php.net/manual/en/function.fread.php#84115
     */
    function sendResumableFile($file, $is_resume = TRUE, $media_type = 'application/force-download')
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
        $filename = (strstr(GeneralFunctions::getValue($_SERVER, 'HTTP_USER_AGENT', ''), 'MSIE')) ?
        preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
        $fileinfo['basename'];

        //check if http_range is sent by browser (or download manager)
        if ($is_resume && isset($_SERVER['HTTP_RANGE'])) {
            list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if ($size_unit == 'bytes') {
                // According to the spec, you could request several ranges here.
                // For simplicity, just send the first one.
                $ranges = explode(',', $range_orig);
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
            $seek_start = $seek[0];
        } else {
            $seek_start = '';
        }
        if (isset($seek[1])) {
            $seek_end = $seek[1];
        } else {
            $seek_end = '';
        }

        //set start and end based on range (if set), else set defaults
        //also check for invalid ranges.
        $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
        $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

        //add headers if resumable
        if ($is_resume) {
            //Only send partial content header if downloading a piece of the file (IE workaround)
            if ($seek_start > 0 || $seek_end < ($size - 1)) {
                header('HTTP/1.1 206 Partial Content');
            }

            header('Accept-Ranges: bytes');
            header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
        }

        header('Content-Type: ' . $media_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: '.($seek_end - $seek_start + 1));

        //open the file
        $fp = fopen($file, 'rb');
        //seek to start of missing part
        fseek($fp, $seek_start);

        //start buffered download
        while (!feof($fp)) {
            //reset time limit for big files
            set_time_limit(0);
            print(fread($fp, 1024*8));
            flush();
            ob_flush();
        }

        fclose($fp);
        exit;
    }

    /**
     * Return UTF8 encoded array
     *
     * @param array|object|string|integer|float|boolean|null $array Ideally an 
     * array of data to process, but failing that, return the data as a member of an array.
     *
     * @return array UTF8 encoded array
     *
     * @link http://www.php.net/manual/en/function.json-encode.php#99837
     */
    function utf8element($array = null)
    {
        $newArray = array();
        if (is_object($array)) {
            // Force objects to be recast as an array
            $array = (array) $array;
        } elseif (is_array($array)) {
            // It's an array already, we don't need to mangle it.
        } else {
            // Individual items should be recast to be the only item in an array
            $array = array($array);
        }
        foreach ($array as $key=>$val) {
            if (is_array($val) || is_object($val)) {
                $newArray[utf8_encode($key)] = static::utf8element($val);
            } elseif ($val === false) {
                $newArray[utf8_encode($key)] = 'false';
            } elseif ($val == null) {
                $newArray[utf8_encode($key)] = '';
            } else {
                $newArray[utf8_encode($key)] = utf8_encode($val);
            }
        }
        return $newArray;
    }

    /**
     * Return utf8 encoded JSON
     *
     * @param Array|object $array Incoming data
     *
     * @return string UTF8 encoded JSON string
     */
    function utf8json($array = array())
    {
        return json_encode(static::utf8element($array));
    }

    /**
     * Return utf8 encoded HTML
     *
     * @param Array|object $array Incoming data
     *
     * @return string UTF8 encoded HTML tables
     */
    function utf8html($array = array())
    {
        return static::html_encode(static::utf8element($array));
    }

    /**
     * Similar to the json_encode function, this returns nested HTML tables instead of nested JSON data
     * 
     * @param array $array Data to encode
     * 
     * @return string HTML nested tables of the array of data
     */
    function html_encode($array = array())
    {
        $return = '<table>';
        foreach ($array as $key => $item) {
            $return .= '<tr><th>' . $key . '</th><td>';
            if (is_array($item)) {
                $return .= static::html_encode($item);
            } else {
                $return .= $item;
            }
            $return .= '</td></tr>';
        }
        $return .= '</table>';
        return $return;
    }

    /**
     * Return utf8 encoded XML with an optional root element name
     *
     * @param Array|object $array Incoming data
     * @param string       $root  The root element name - default to "row"
     *
     * @return string UTF8 encoded XML string
     */
    function utf8xml($array = array(), $root = 'row')
    {
        return static::xml_encode(array($root => static::utf8element($array)));
    }
    
    /**
     * Similar to the json_encode function, this returns nested XML stanzas.
     * It doesn't have the concept of parameters. Also, replaces a forward slash with "[slash]"
     * 
     * @param array   $array Data to encode
     * @param integer $depth The number of spaces to indent each nested stanza by
     * 
     * @return string XML formatted data
     */
    function xml_encode($array = array(), $depth = 0)
    {
        $return = '';
        foreach ($array as $key => $item) {
            if (is_integer($key)) {
                $key = 'ID_' . $key;
            }
            $key = str_replace('/', '[slash]', $key);
            $key = str_replace('<', '[lt]', $key);
            $key = str_replace('>', '[gt]', $key);
            $key = str_replace('&', '[amp]', $key);
            $key = str_replace('"', '[dquote]', $key);
            $key = str_replace("'", '[squote]', $key);
            $return .= str_repeat(' ', $depth) . '<' . $key . ">";
            if (is_array($item)) {
                $return .= "\r\n" . static::xml_encode($item, $depth + 4) . str_repeat(' ', $depth);
            } else {
                $return .= $item;
            }
            $return .= '</' . $key . ">\r\n";
        }
        return $return;
    }
    
    /**
    * Do a redirection to the $new_page (relative to the base URI of the site)
    *
    * @param string $new_page New page to refer to
    *
    * @return void
    */
    function redirectTo($new_page = '')
    {
        $arrRequestDetails = Base_Request::getRequest();
        if (substr($new_page, 0, 1) != '/') {
            $new_page = '/' . $new_page;
        }
        if (substr($arrRequestDetails['basePath'], -1) == '/') {
            $arrRequestDetails['basePath'] = substr($arrRequestDetails['basePath'], 0, -1);
        }
        $redirect_url = $arrRequestDetails['basePath'] . $new_page;
        header("Location: $redirect_url");
        exit(0);
    }
}