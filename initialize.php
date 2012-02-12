<?php

require_once dirname(__FILE__) . '/classes/autoloader.php';
base_config::initialize();
foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/object') as $file) {
    if ($file->isDir() || $file->isDot()) continue;
    if ($file->isFile() && ($file->getBasename('.php') != $file->getBasename())) {
        $classname = 'object_' . $file->getBasename('.php');
        $class = new $classname();
        $class->initialize();
    }
}