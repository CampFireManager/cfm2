<?php

require_once dirname(__FILE__) . '/classes/autoloader.php';
Base_Config::initializeDemo();
foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/Object') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $classname = 'Object_' . $file->getBasename('.php');
        $class = new $classname();
        $class->initializeDemo();
    }
}