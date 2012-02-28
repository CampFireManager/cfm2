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
        $media = Base_Request::getMediaType('media');
        if (! $media) {
            Base_Response::sendHttpResponse(404, null, $arrRequestData['strPreferredAcceptType']);
        }
        $file = Base_Config::getConfigLocal('strMediaPath', dirname(__FILE__) . '/media');
        foreach ($arrRequestData['pathItems'] as $key => $pathItem) {
            if ($key != 0) {
                if ($pathItem == '..') {
                    Base_Response::sendHttpResponse(403, null, $arrRequestData['strPreferredAcceptType']);
                }
                $file .= '/' . $pathItem;
            }
        }
        if (is_file($file)) {
            Base_Response::dl_file_resumable($file, TRUE, $arrRequestData['strPreferredAcceptType']);
        }
    }
    if ($arrRequestData['pathItems'][0] == 'rest' && Base_Request::getMediaType('rest')) {
        unset($arrRequestData['pathItems'][0]);
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
            $useObjects[$arrValidObjects[$pathItem]] = null;
            $lastObject = $pathItem;
            $renderPage = $arrValidObjects[$pathItem];
        } elseif ($lastObject != null) {
            $useObjects[$arrValidObjects[$lastObject]] = $pathItem;
            $lastObject = null;
        }
    }

    foreach ($useObjects as $object => $item) {
        if ($item == null) {
            $arrObjects[$object] = $object::brokerAll();
        } else {
            $arrObjects[$object][$item] = $object::brokerByID($item);
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
    }
} else {
    Base_TemplateLoader::render($renderPage, $arrObjectsData);
}
