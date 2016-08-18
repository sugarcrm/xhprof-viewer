<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require 'src/php/bootstrap.php';

\Sugarcrm\XHProf\Viewer\DependencyInjection\Factory::getContainer()
    ->get('Sugarcrm\XHProf\Viewer\Controllers\FrontController')
    ->dispatch();
