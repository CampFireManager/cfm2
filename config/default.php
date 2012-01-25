<?php

$RW_TYPE = 'mysql';
$RW_HOST = '127.0.0.1';
$RW_PORT = '3306';
$RW_BASE = 'database';
$RW_USER = 'root';
$RW_PASS = '';

$SPLIT_RO_RW = false;

$RO_TYPE = '';
$RO_HOST = '';
$RO_PORT = '';
$RO_BASE = '';
$RO_USER = '';
$RO_PASS = '';

$APPCONFIG = array();

if (file_exists(dirname(__FILE__) . "/local.php")) {
    include dirname(__FILE__) . "/local.php";
}

if (!isset($RW_DSN)) {
    $RW_DSN = array(
        'string' => "$RW_TYPE:host=$RW_HOST;port=$RW_PORT;dbname=$RW_BASE",
        'user' => $RW_USER,
        'pass' => $RW_PASS
    );
}

if (!isset($RO_DSN) and $SPLIT_RO_RW == true) {
    $RO_DSN = array(
        'string' => "$RO_TYPE:host=$RO_HOST;port=$RO_PORT;dbname=$RO_BASE",
        'user' => $RO_USER,
        'pass' => $RO_PASS
    );
}