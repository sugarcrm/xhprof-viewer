<?php

$config = array(
    'version' => 'VIEWER_VERSION',
    'profile_files_dir' => '/tmp/profile_files',
);

if (file_exists('config_override.php')) {
    require 'config_override.php';
}

return $config;
