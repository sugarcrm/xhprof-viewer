<?php

namespace Sugarcrm\XHProf\Viewer\Templates\RunsList;

use \Sugarcrm\XHProf\Viewer\Helpers\VersionHelper;


class Version
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
            if ($version['type'] == 'hash') {
                echo substr($version['version'], 0, 7);
            } else {
                echo 'v', $version['version'];
            }
        }
    }
}
