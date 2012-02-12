<?php
/**
 * Missing Function Finder is a script to ensure all referenced functions are included in all the scripts.
 *
 * PHP version 5
 *
 * @category Testing
 * @package  CCHitsTests
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     http://gitorious.net/cchits-net Version Control Service
 */
if ( ! isset($argv[1]) or $argv[1] == '') {
    showHelp("No directory specified.");

    exit(255);
}
if ($argv[1] == '--help' or $argv[1] == '-h' or $argv[1] == '/?') {
    showHelp();
    exit(1);
}

$finder = array();
$filecontent = array();
$classfunction = array();

for ($arg = 1; $arg < $argc; $arg++) {
    $finder = array_merge(parseDir($argv[$arg], $finder));
}

foreach ($finder as $file=>$dummy) {
    $data = parseFile($file);
    if (isset($data['class']) and count($data['class']) > 0) {
        foreach ($data['class'] as $filename=>$class) {
            $filecontent['class'][$filename] = $class;
        }
    }
    if (isset($data['function']) and count($data['function']) > 0) {
        foreach ($data['function'] as $filename=>$function) {
            $filecontent['function'][$filename] = $function;
        }
    }
}

if (isset($filecontent['class']) and count($filecontent['class']) > 0) {
    foreach ($filecontent['class'] as $file=>$matches) {
        foreach ($matches as $class=>$arrfunction) {
            foreach ($arrfunction as $function=>$line) {
                $classfunction[$class][$function][$file] = $line;
            }
        }
    }
}

if (isset($filecontent['function']) and count($filecontent['function']) > 0) {
    foreach ($filecontent['function'] as $file=>$matches) {
        foreach ($matches as $class=>$arrfunction) {
            foreach ($arrfunction as $function=>$dummy) {
                $classfunction[$class][$function][$file] = true;
            }
        }
    }
}

if (count($classfunction) > 0) {
    foreach ($classfunction as $class=>$arrfunction) {
        foreach ($arrfunction as $function=>$arrfile) {
            $data = "{$class}::{$function}: ";
            $files = "\t";
            $first = true;
            $isdefined = false;
            foreach ($arrfile as $file=>$defined) {
                if (!$first) {
                    $files .= ", ";
                }
                $first = false;
                if ($defined === true) {
                    $isdefined = true;
                } else {
                    $files .= $file . ':' . $defined;
                }
            }
            if ($isdefined == false) {
                echo $data . $files . "\r\n";
            }
        }
    }
}

/**
 * A function to render some help text.
 *
 * @param string $error An optional reason for exiting the test.
 *
 * @return void
 */
function showHelp($error = '')
{
    if ($error != '') {
        echo "Failed: $error\n";
        echo "Run {$argv[0]} [/path/to/dir]";
    }
}

/**
 * A function to return a list of files
 *
 * @param string $dirname The directory to parse
 *
 * @return array An array of files
 */
function parseDir($dirname = '')
{
    $return = array();
    if (file_exists($dirname) and $handle = opendir($dirname)) {
        while (false !== ($file = readdir($handle))) {
            if (substr($file, 0, 1) == '.') {
                // Do nothing
            } else {
                if (is_dir($dirname . '/' . $file)) {
                    $return = array_merge(parseDir($dirname . '/' . $file), $return);
                } else {
                    $return[$dirname . '/' . $file] = true;
                }
            }
        }
        closedir($handle);
    }
    ksort($return);
    return $return;
}

/**
 * A function to parse the files for functions and classes
 *
 * @param string $filename The file to parse
 *
 * @return array Parsed data
 */
function parseFile($filename = '')
{
    $classname = "none";
    if (! file_exists($filename)) {
        return false;
    }
    $arrfile = file($filename);
    $return = array();
    $singletonmatches = array();
    $classmatches = array();
    $classes = array();
    $extends = '';
    foreach ($arrfile as $line=>$file) {
        preg_match_all('/(^|abstract)\s*class (\S+)(.*)/', $file, $arrclassname, PREG_SET_ORDER);
        if (count($arrclassname) > 0) {
            $arrclassname = end($arrclassname);
            if ($arrclassname[3] != '') {
                preg_match_all('/\s*extends (\S+)/', $arrclassname[3], $arrparentclassname, PREG_SET_ORDER);
                if (count($arrparentclassname) > 0) {
                    $arrparentclassname = end($arrparentclassname);
                    $extends = $arrparentclassname[1];
                }
            }
            $classname = $arrclassname[2];
            $return['function'][$filename][$classname]['__construct'] = $line;
        }
        preg_match_all('/function ([^\(]+)\(/', $file, $functions, PREG_SET_ORDER);
        if (count($functions) > 0) {
            $functions = $functions[0];
            $return['function'][$filename][$classname][$functions[1]] = $line;
        }
        preg_match_all('/(\w+)::([^\$^\(]+)*\(/', $file, $singletonmatches, PREG_SET_ORDER);
        if (count($singletonmatches) > 0) {
            foreach ($singletonmatches as $match) {
                if ($match[1] == 'self') {
                    $match[1] = $classname;
                } elseif ($match[1] == 'parent') {
                    $match[1] = $extends;
                }
                switch($match[1]) {
                case 'PDO':
                case 'Smarty':
                case 'Exception':
                case 'QRcode':
                case 'Auth_OpenID_Consumer':
                case 'Auth_OpenID_FileStore':
                case 'Auth_OpenID_AX_AttrInfo':
                case 'Auth_OpenID_SRegRequest':
                case 'Auth_OpenID_SRegResponse':
                case 'Auth_OpenID_AX_FetchResponse':
                    break;
                default:
                    $return['class'][$filename][$match[1]][$match[2]] = $line;
                }
            }
        }
        preg_match_all('/(\$\S+)\s*=\s*new ([^\(^\s]+)\(/', $file, $classmatches, PREG_SET_ORDER);
        preg_match_all('/(\$\S+)\s*->([^\$^\(^\s]*)\(/', $file, $functionmatches, PREG_SET_ORDER);
        if (count($classmatches) > 0) {
            foreach ($classmatches as $classmatch) {
                if ($classmatch[2] == 'self') {
                    $classmatch[2] = $classname;
                } elseif ($classmatch[2] == 'parent') {
                    $classmatch[2] = $extends;
                }
                $classes[$classmatch[1]] = $classmatch[2];
                switch($classmatch[2]) {
                case 'PDO':
                case 'Smarty':
                case 'Exception':
                case 'QRcode':
                case 'Auth_OpenID_Consumer':
                case 'Auth_OpenID_FileStore':
                case 'Auth_OpenID_AX_AttrInfo':
                case 'Auth_OpenID_SRegRequest':
                case 'Auth_OpenID_SRegResponse':
                case 'Auth_OpenID_AX_FetchResponse':
                    break;
                default:
                    $return['class'][$filename][$classmatch[2]]['__construct'] = $line;
                }
            }
            foreach ($functionmatches as $functionmatch) {
                if (isset($classes[$functionmatch[1]])) {
                    $return['class'][$filename][$classes[$functionmatch[1]]][$functionmatch[2]] = $line;
                }
            }
        }
    }
    return $return;
}