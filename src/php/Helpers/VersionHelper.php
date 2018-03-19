<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Helpers;

/**
 * Version Helper
 */
class VersionHelper
{
    /**
     * Returns array of current version type and version
     *
     * @return array|bool
     */
    public function getCurrentVersion()
    {
        global $config;
        $version = $config['version'];
        return array('type' => 'version', 'version' => $version);
    }
}
