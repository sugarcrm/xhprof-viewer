<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class UrlHelper
{
    public static function url($params)
    {
        return '?' . http_build_query($params);
    }
}
