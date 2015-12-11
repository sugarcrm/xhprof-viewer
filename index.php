<?php

$instanceRoot = __DIR__ . '/';
$XHPProfLibRoot = "$instanceRoot/xhprof/xhprof_lib/";

require_once "$instanceRoot/config.php";

session_start();

require_once "{$XHPProfLibRoot}display/xhprof.php";
require_once "{$XHPProfLibRoot}/utils/xhprof_runs.php";
require_once "CustomViewXhProf.php";

$x = new CustomViewXhProf();
$x->display();
