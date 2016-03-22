<?php

require 'vendor/autoload.php';

$instanceRoot = __DIR__;
$XHPProfLibRoot = "$instanceRoot/xhprof/xhprof_lib/";

$config = require "$instanceRoot/config.php";

require_once "{$XHPProfLibRoot}/display/xhprof.php";

$fc = new \Sugarcrm\XHProf\Viewer\Controllers\FrontController();
$fc->dispatch();
