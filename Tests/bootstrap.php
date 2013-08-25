<?php
require_once dirname(__FILE__) . '/../classes/autoloader.php';
session_save_path('/tmp');
ini_set('session.gc_probability', 1);