<?php

$generator = microtime(true);

require_once dirname(__FILE__) . '/classes/autoloader.php';

$arrRequest = Base_Request::getRequest();

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
$useObject = array();

foreach ($arrRequest['pathItems'] as $pathItem) {
    if ($pathItem == 'resources') {
        $rest = true;
    }
    if (isset($object[$pathItem])) {
        $useObject[$object[$pathItem]] = null;
        $lastObject = $pathItem;
    } elseif ($lastObject != null) {
        $useObject[$object[$lastObject]] = $pathItem;
        $lastObject = null;
    }
}

var_dump(array('RESTful' => $rest, 'User' => Object_User::brokerCurrent(), 'Objects' => $useObject, 'generator' => round(microtime(true) - $generator, 3) . ' seconds'));
