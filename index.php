<?php

require_once(dirname(__FILE__) . '/classes/autoloader.php');

$arrRequest = base_request::getRequest();

foreach ($arrRequest['pathItems'] as $pathItem) {
    if ($pathItem == 'api') {
        base_hook::triggerHook('apiRender', $arrRequest);
    } else {
        base_hook::triggerHook('httpRender', $arrRequest);
    }
}