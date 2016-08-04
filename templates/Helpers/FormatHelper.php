<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class FormatHelper
{
    public static function microsec($m)
    {
        return static::number($m) . 'μs';
    }

    public static function number($number)
    {
        return number_format($number);
    }
}
