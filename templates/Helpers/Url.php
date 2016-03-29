<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


use Sugarcrm\XHProf\Viewer\Controllers\AbstractController;

class Url
{
    /**
     * @var AbstractController
     */
    protected static $currentController;

    /**
     * @return AbstractController
     */
    public static function getCurrentController()
    {
        return self::$currentController;
    }

    /**
     * @param AbstractController $currentController
     */
    public static function setCurrentController($currentController)
    {
        self::$currentController = $currentController;
    }

    public static function thisPageUrl($override = array(), $drop = array())
    {
        $controller = static::getCurrentController();
        $params = array();
        $list = $controller->getParamsList();
        foreach ($list as $param) {
            $params[$param] = $controller->getParam($param);
        }

        foreach ($override as $key => $value) {
            $params[$key] = $value;
        }

        $defaults = $controller->getParamDefaults();
        foreach ($params as $param => $value) {
            if (isset($defaults[$param]) && $defaults[$param] == $value) {
                $drop[] = $param;
            }
        }

        foreach ($drop as $param) {
            unset($params[$param]);
        }

        return static::url($params);
    }

    public static function url($params)
    {
        return '?' . http_build_query($params);
    }
}
