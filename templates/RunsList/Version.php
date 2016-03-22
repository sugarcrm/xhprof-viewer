<?php

namespace Sugarcrm\XHProf\Viewer\Templates\RunsList;

use \Sugarcrm\XHProf\Viewer\Helpers\VersionHelper;


class Version
{
    public static function render(VersionHelper $versionHelper) {
        if ($version = $versionHelper->getCurrentVersion()) {
            if ($version['type'] == 'hash') {
                echo substr($version['version'], 0, 7);
            } else {
                echo 'v', $version['version'];
            }
        }
    }
}
