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

$generator = microtime(true);
/**
 * This file defines the autoloader for the classes mentioned elsewhere.
 */
require_once dirname(__FILE__) . '/classes/autoloader.php';

$arrRequestData = Base_Request::getRequest();
$arrMediaType = explode('/', $arrRequestData['strPreferredAcceptType']);

// What type of request is this
$rest = false;
$media = false;

if (is_array($arrRequestData['pathItems']) && count($arrRequestData['pathItems']) > 0) {
    if ($arrRequestData['pathItems'][0] == 'media') {
        unset($arrRequestData['pathItems'][0]);
        $tmpPathItems = array();
        foreach ($arrRequestData['pathItems'] as $data) {
            $tmpPathItems[] = $data;
        }
        $arrRequestData['pathItems'] = $tmpPathItems;
        $media = Base_Request::getMediaType('media');
        if (! $media) {
            Base_Response::sendHttpResponse(404, null, $arrRequestData['strPreferredAcceptType']);
        }
        $file = Base_Config::getConfigLocal('strMediaPath', dirname(__FILE__) . '/Media');
        foreach ($arrRequestData['pathItems'] as $key => $pathItem) {
            if ($pathItem == '..') {
                Base_Response::sendHttpResponse(403, null, $arrRequestData['strPreferredAcceptType']);
            }
            $file .= '/' . $pathItem;
        }
        if (isset($arrRequestData['pathFormat']) && $arrRequestData['pathFormat'] != '') {
            $file .= '.' . $arrRequestData['pathFormat'];
        }
        if (is_file($file)) {
            Base_Response::dl_file_resumable($file, TRUE, $arrRequestData['strPreferredAcceptType']);
        }
    }
    if ($arrRequestData['pathItems'][0] == 'openid') {
        if (isset($_POST['id'])) {
            Base_OpenID::request($_POST['id'], $arrRequestData['basePath'] . $arrRequestData['pathSite'] . 'openid', $arrRequestData['basePath'] . $arrRequestData['pathSite'], $arrRequestData['basePath'] . $arrRequestData['pathSite']);
        } elseif (isset($_REQUEST['return'])) {
            Base_OpenID::response($arrRequestData['basePath'] . $arrRequestData['pathSite'] . 'openid');
        } elseif (isset($_GET['logout'])) {
            if ($arrObjects['Object_User']['current'] != false) {
                if (isset($arrRequestData['requestUrlParameters']['logout'])) {
                    $arrObjects['Object_User']['current']->logout();
                    Base_Response::redirectTo('timetable');
                }
            }
        } else {
            Base_Response::redirectTo('timetable');
        }
    }
    if ($arrRequestData['pathItems'][0] == 'rest' && Base_Request::getMediaType('rest')) {
        unset($arrRequestData['pathItems'][0]);
        $tmpPathItems = array();
        foreach ($arrRequestData['pathItems'] as $data) {
            $tmpPathItems[] = $data;
        }
        $arrRequestData['pathItems'] = $tmpPathItems;
        $rest = true;
    }
}
    
// What type of objects can we request
$arrValidObjects = array('config' => 'Base_Config');

foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/Object') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $arrValidObjects[strtolower($file->getBasename('.php'))] = 'Object_' . $file->getBasename('.php');
    }
}

foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/Collection') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $arrValidObjects[strtolower($file->getBasename('.php'))] = 'Collection_' . $file->getBasename('.php');
    }
}

/**
 * A value which stores the last processed object type
 * @var string
 */
$lastObject = null;
/**
 * An array of objects requested
 * @var array
 */
$useObjects = array();
/**
 * An array of the processed requested objects
 * @var array
 */
$arrObjects = array();
/**
 * An array containing the values from the requested objects
 * @var array
 */
$arrObjectsData = array();
/**
 * Load the template of the last object type requested. This value stores what
 * that was.
 * @var string
 */
$renderPage = null;

if (is_array($arrRequestData['pathItems']) && count($arrRequestData['pathItems']) > 0 && $arrRequestData['pathItems'][0] != '') {
    foreach ($arrRequestData['pathItems'] as $pathItem) {
        if (isset($arrValidObjects[$pathItem])) {
            if ($renderPage == null) {
                $useObjects[$arrValidObjects[$pathItem]] = null;
                $lastObject = $pathItem;
                $renderPage = $arrValidObjects[$pathItem];
            }
        } elseif ($lastObject != null) {
            $useObjects[$arrValidObjects[$lastObject]] = $pathItem;
            $lastObject = null;
        } else {
            $lastObject = null;
        }
    }

    foreach ($useObjects as $object => $item) {
        if ($item == null) {
            switch ($arrRequestData['method']) {
            case 'head':
            case 'get':
                $arrObjects[$object] = $object::brokerAll();
                break;
            case 'post':
            case 'put':
                $newobject = new $object(false);
                foreach ($arrRequestData['requestUrlParameters'] as $key => $value) {
                    $newobject->setKey($key, $value);
                }
                try {
                    $newobject->create();
                    $key = $newobject->getPrimaryKeyValue();
                    if ($key == '') {
                        throw new Exception("Although the object was created, we didn't receive a primary key for it. Values are: " . print_r($newobject->getSelf()));
                    } else {
                        $arrType = explode('_', $object);
                        $object_type = strtolower($arrType[1]);
                        Base_Response::redirectTo($object_type . '/' . $key);
                    }
                } catch (Exception $e) {
                    error_log("Unable to create new object of type $object due to error " . $e->getMessage());
                    Base_Response::sendHttpResponse(406);
                }
                break;
            case 'delete':
                Base_Response::sendHttpResponse(405);
            }
        } else {
            $requestedobject = $object::brokerByID($item);
            switch ($arrRequestData['method']) {
            case 'head':
            case 'get':
                $arrObjects[$object][$item] = $requestedobject;
                break;
            case 'post':
            case 'put':
                if ($requestedobject == false) {
                    Base_Response::sendHttpResponse(404);
                } else {
                    foreach ($arrRequestData['requestUrlParameters'] as $key => $value) {
                        $requestedobject->setKey($key, $value);
                    }
                    try {
                        $requestedobject->write();
                        $arrType = explode('_', $object);
                        $object_type = strtolower($arrType[1]);
                        Base_Response::redirectTo($object_type . '/' . $item);
                    } catch (Exception $e) {
                        error_log("Unable to update object of type $object, item code $item due to error " . $e->getMessage());
                        Base_Response::sendHttpResponse(406);
                    }
                }
                break;
            case 'delete':
                if ($requestedobject == false) {
                    Base_Response::sendHttpResponse(404);
                } else {
                    try {
                        $requestedobject->delete();
                        Base_Response::redirectTo('timetable');
                    } catch (Exception $e) {
                        error_log("Unable to update object of type $object, item code $item due to error " . $e->getMessage());
                        Base_Response::sendHttpResponse(406);
                    }
                }
            }
        }
    }
} else {
    Base_Response::redirectTo('timetable');
}

$useObjects['Object_User'] = 'current';
$arrObjects['Object_User']['current'] = Object_User::brokerCurrent();

foreach ($arrObjects as $object_group => $data) {
    foreach ($data as $key => $object) {
        if (is_object($object)) {
            $object->setFull(true);
            $arrObjectsData[$object_group][$key] = $object->getSelf();
        } else {
            $arrObjectsData[$object_group][$key] = null;
        }
    }
}

if ($rest) {
    switch ($arrRequestData['strPreferredAcceptType']) {
    case 'application/json':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8json($arrObjectsData), $arrRequestData['strPreferredAcceptType']);
        break;
    case 'text/xml':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8xml($arrObjectsData), $arrRequestData['strPreferredAcceptType']);
        break;
    case 'text/html':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8html($arrObjectsData), $arrRequestData['strPreferredAcceptType']);
        break;
    // I'd like to add RDFa or TTL files here, but I need to work out how to set the data up for that.
    }
} else {
    Base_TemplateLoader::render($renderPage, $arrObjectsData);
}
