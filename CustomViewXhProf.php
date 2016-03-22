<?php

class CustomViewXhProf
{
    /**
     * @var \Sugarcrm\XHProf\Viewer\Storage\StorageInterface
     */
    protected $storage;


    public function __construct()
    {
        $this->storage = new \Sugarcrm\XHProf\Viewer\Storage\FileStorage();
    }

    public function display()
    {
        header('Content-type: text/html; charset=utf-8');

        if (!empty($_REQUEST['dir'])) {
            $this->storage->setCurrentDirectory($_REQUEST['dir']);
        }

        if (isset($_GET['q'])) {
            $controller = new \Sugarcrm\XHProf\Viewer\Controllers\TypeAheadController();
        } else if (isset($_GET['callgraph'])) {
            $controller = new \Sugarcrm\XHProf\Viewer\Controllers\CallGraphController();
        } else if (isset($_GET['run'])) {
            $controller = new \Sugarcrm\XHProf\Viewer\Controllers\RunController();
        } else {
            $controller = new \Sugarcrm\XHProf\Viewer\Controllers\RunsListController();
        }

        $controller->setStorage($this->storage);
        $controller->indexAction();
    }
}
