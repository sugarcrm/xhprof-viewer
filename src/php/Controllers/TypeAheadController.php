<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


class TypeAheadController
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

    /**
     * Return suggested symbols based on the query $q
     *
     * @param $run string run id against which to search
     * @param $q string query string
     */
    public function indexAction($run, $q)
    {
        header('Content-Type: application/json');
        echo json_encode($this->storage->getRunXHprofMatchingFunctions($run, $q));
    }
}
