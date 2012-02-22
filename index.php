<?php

$generator = microtime(true);

require_once dirname(__FILE__) . '/classes/autoloader.php';

$arrRequestData = Base_Request::getRequest();

// What type of request is this
$rest = false;
// What type of objects can we request
$object = array('config' => 'Base_Config');

foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/Object') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $object[strtolower($file->getBasename('.php'))] = 'Object_' . $file->getBasename('.php');
    }
}

$lastObject = null;
$useObjects = array();
$arrObjects = array();
$arrObjectsData = array();

if (is_array($arrRequestData['pathItems']) && count($arrRequestData['pathItems']) > 0) {
    foreach ($arrRequestData['pathItems'] as $pathItem) {
        if ($pathItem == 'rest') {
            $rest = true;
        }
        if (isset($object[$pathItem])) {
            $useObjects[$object[$pathItem]] = null;
            $lastObject = $pathItem;
        } elseif ($lastObject != null) {
            $useObjects[$object[$lastObject]] = $pathItem;
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
}


$useObjects['Object_User'] = 'current';
$arrObjects['Object_User']['current'] = Object_User::brokerCurrent();

foreach ($arrObjects as $object_group => $data) {
    foreach ($data as $key => $object) {
        if ($object !== false && $object !== null) {
            $object->setFull(true);
            $arrObjectsData[$object_group][$key] = $object->getSelf();
        } else {
            $arrObjectsData[$object_group][$key] = null;
        }
    }
}

var_dump(
        array(
            'RESTful' => $rest, 
            'Objects' => array(
                'Items' => $useObjects, 
                'Objects' => $arrObjectsData
                ), 
            'generator' => round(microtime(true) - $generator, 3) . ' seconds'
            )
        );
