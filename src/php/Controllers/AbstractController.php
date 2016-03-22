<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


abstract class AbstractController
{
    /**
     * @var \Sugarcrm\XHProf\Viewer\Storage\StorageInterface
     */
    protected $storage;

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
}
