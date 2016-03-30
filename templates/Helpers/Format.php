<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class Format
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
