<?php

require 'vendor/autoload.php';

require_once 'xhprof/xhprof_lib/utils/callgraph_utils.php';
require_once 'xhprof/xhprof_lib/display/xhprof.php';

$config = require 'config.php';

$fc = new \Sugarcrm\XHProf\Viewer\Controllers\FrontController();
$fc->dispatch();
