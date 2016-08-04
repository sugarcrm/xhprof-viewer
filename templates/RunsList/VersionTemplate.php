<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\RunsList;

use \Sugarcrm\XHProf\Viewer\Helpers\VersionHelper;


class VersionTemplate
{
    /**
     * @var VersionHelper
     */
    protected static $versionHelper;

    /**
     * @return VersionHelper
     */
    public static function getVersionHelper()
    {
        if (!static::$versionHelper) {
            static::$versionHelper = new VersionHelper();
        }
        return static::$versionHelper;
    }

    /**
     * @param VersionHelper $versionHelper
     */
    public static function setVersionHelper($versionHelper)
    {
        static::$versionHelper = $versionHelper;
    }

    public static function render() {
        if ($version = static::getVersionHelper()->getCurrentVersion()) {
            echo '<p>';
            if ($version['type'] == 'hash') {
                echo substr($version['version'], 0, 7);
            } else {
                echo 'v', $version['version'];
            }
            echo '</p>';
        }
    }
}
