<?php

$config = array(
    'version' => '1.0.4',
    'profile_files_dir' => '/tmp/profile_files',
);

if (file_exists('config_override.php')) {
    require 'config_override.php';
}

return $config;
