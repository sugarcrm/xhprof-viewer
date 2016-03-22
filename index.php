<?php

require 'vendor/autoload.php';

$config = require 'config.php';

$XHPProfLibRoot = __DIR__ . "/xhprof/xhprof_lib/";
require_once "{$XHPProfLibRoot}/display/xhprof.php";

$fc = new \Sugarcrm\XHProf\Viewer\Controllers\FrontController();
$fc->dispatch();
