<?php

require 'vendor/autoload.php';

$instanceRoot = __DIR__;
$XHPProfLibRoot = "$instanceRoot/xhprof/xhprof_lib/";

$config = require "$instanceRoot/config.php";

require_once "{$XHPProfLibRoot}/display/xhprof.php";
require_once "{$XHPProfLibRoot}/utils/xhprof_runs.php";
require_once "CustomViewXhProf.php";

$x = new CustomViewXhProf();
$x->display();
