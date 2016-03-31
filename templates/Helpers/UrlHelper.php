<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class UrlHelper
{
    public static function url($params)
    {
        return '?' . http_build_query($params);
    }
}
