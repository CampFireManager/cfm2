<?php

require_once(dirname(__FILE__) . '/classes/autoloader.php');

$arrRequest = base_request::getRequest();

var_dump(object_user::brokerCurrent());

$api = false;
$html = false;

foreach ($arrRequest['pathItems'] as $pathItem) {
    if ($pathItem == 'api') {
        $api = true;
    } else {
        $html = true;
    }

}

