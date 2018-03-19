<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

$config = array(
    'version' => '1.1.0',
    'profile_files_dir' => '/tmp/profile_files',
);

if (file_exists('config_override.php')) {
    require 'config_override.php';
}

return $config;
