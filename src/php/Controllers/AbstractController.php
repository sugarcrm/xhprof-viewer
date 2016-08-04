<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Controllers;


abstract class AbstractController
{
    /**
     * @var \Sugarcrm\XHProf\Viewer\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    protected $paramsList;

    /**
     * @var array
     */
    protected $paramDefaults;

    /**
     * @return \Sugarcrm\XHProf\Viewer\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param \Sugarcrm\XHProf\Viewer\Storage\StorageInterface $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        $value = null;
        if (in_array($name, $this->paramsList)) {
            if (isset($_REQUEST[$name])) {
                $value = $_REQUEST[$name];
            } elseif (isset($this->paramDefaults[$name])) {
                $value = $this->paramDefaults[$name];
            }
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getParamsList()
    {
        return $this->paramsList;
    }

    /**
     * @return array
     */
    public function getParamDefaults()
    {
        return $this->paramDefaults;
    }
}
