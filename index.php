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
 * This file defines the autoloader for the classes mentioned elsewhere.
 */
require_once dirname(__FILE__) . '/classes/autoloader.php';
Base_Response::setGenerationTime();
Container_Config::LoadConfig();

$objRequest = Container_Request::getRequest();
$arrMediaType = explode('/', $objRequest->get_strPrefAcceptType());

if (Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'logout', false, false) != false) {
    Object_User::logout();
    Base_Response::redirectTo('timetable');
}

// What type of request is this
$rest = false;
$media = false;

if (is_array($objRequest->get_arrPathItems()) && count($objRequest->get_arrPathItems()) > 0) {
    $arrPathItems = $objRequest->get_arrPathItems();
    if ($arrPathItems[0] == 'media') {
        unset($arrPathItems[0]);
        $tmpPathItems = array();
        foreach ($arrPathItems as $data) {
            $tmpPathItems[] = $data;
        }
        $arrPathItems = $tmpPathItems;
        $media = $objRequest->hasMediaType('media');
        if (! $media) {
            Base_Response::sendHttpResponse(404, null, $objRequest->get_strPrefAcceptType());
        }
        $file = Container_Config::brokerByID('strMediaPath', dirname(__FILE__) . '/Media')->getKey('value');
        foreach ($arrPathItems as $key => $pathItem) {
            if ($pathItem == '..') {
                Base_Response::sendHttpResponse(403, null, $objRequest->get_strPrefAcceptType());
            }
            $file .= '/' . $pathItem;
        }
        if ($objRequest->get_strPathFormat() != '') {
            $file .= '.' . $objRequest->get_strPathFormat();
        }
        if (is_file($file)) {
            Base_Response::sendResumableFile($file, TRUE, $objRequest->get_strPrefAcceptType());
        }
    }
    if ($arrPathItems[0] == 'openid') {
        $arrParameters = $objRequest->get_arrRqstParameters();
        if ($objRequest->get_strRequestMethod() == 'post' 
            && Base_GeneralFunctions::getValue($arrParameters, 'id')
        ) {
            Base_OpenID::request(
                Base_GeneralFunctions::getValue($arrParameters, 'id'), 
                $objRequest->get_strBasePath() . 'openid/', 
                $objRequest->get_strBasePath(), 
                $objRequest->get_strBasePath()
            );
        } elseif (isset($arrParameters['return'])) {
            Base_OpenID::response($objRequest->get_strBasePath() . 'openid/');
        } else {
            Base_Response::redirectTo('timetable');
        }
    }
    if ($arrPathItems[0] == 'rest' && $objRequest->hasMediaType('rest')) {
        unset($arrPathItems[0]);
        $tmpPathItems = array();
        foreach ($arrPathItems as $data) {
            $tmpPathItems[] = $data;
        }
        $arrPathItems = $tmpPathItems;
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

$objRequest = Container_Request::getRequest();
$arrObjectsData['SiteConfig']['baseurl'] = $objRequest->get_strBasePath();
$arrObjects['Object_User']['current'] = Object_User::brokerCurrent();

if (is_array($arrPathItems) && count($arrPathItems) > 0 && $arrPathItems[0] != '') {
    foreach ($arrPathItems as $pathItem) {
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
            switch ($objRequest->get_strRequestMethod()) {
            case 'head':
            case 'get':
                $arrObjects[$object] = $object::brokerAll();
                break;
            case 'post':
            case 'put':
                $newobject = new $object(false);
                foreach ($objRequest->get_arrRqstParameters() as $key => $value) {
                    $newobject->setKey($key, $value);
                }
                try {
                    $newobject->create();
                    $key = $newobject->getPrimaryKeyValue();
                    if ($key == '') {
                        throw new Exception("Although the object was created, we didn't receive a primary key for it. Values are: " . print_r($newobject->getSelf(), true));
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
            switch ($objRequest->get_strRequestMethod()) {
            case 'head':
            case 'get':
                $arrObjects[$object][$item] = $requestedobject;
                break;
            case 'post':
            case 'put':
                if ($requestedobject == false) {
                    Base_Response::sendHttpResponse(404);
                } else {
                    foreach ($objRequest->get_arrRqstParameters() as $key => $value) {
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

$arrObjectsData['PageGenerationTime'] = Base_Response::getGenerationTime();
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
        $arrObjectsData['SiteConfig'][$key] = $object->getKey('value');
    }
}

if ($rest) {
    switch ($objRequest->get_strPrefAcceptType()) {
    case 'application/json':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8json($arrObjectsData), $objRequest->get_strPrefAcceptType());
        break;
    case 'text/xml':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8xml($arrObjectsData), $objRequest->get_strPrefAcceptType());
        break;
    case 'text/html':
        Base_Response::sendHttpResponse(200, Base_GeneralFunctions::utf8html($arrObjectsData), $objRequest->get_strPrefAcceptType());
        break;
    // I'd like to add RDFa or TTL files here, but I need to work out how to set the data up for that.
    }
} else {
    Base_Response::render($renderPage, $arrObjectsData);
}
