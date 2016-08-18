<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\DependencyInjection;

class Factory
{
    protected static $containers = array();

    /**
     * @param string $name
     * @return Container
     */
    public static function getContainer($name = '')
    {
        if (!isset(static::$containers[$name])) {
            static::$containers[$name] = new Container();
            static::$containers[$name]->set('container', static::$containers[$name]);
        }

        return static::$containers[$name];
    }
}
