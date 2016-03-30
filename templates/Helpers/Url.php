<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class Url
{
    public static function url($params)
    {
        return '?' . http_build_query($params);
    }
}
