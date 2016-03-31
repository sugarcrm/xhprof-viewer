<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;

use \Sugarcrm\XHProf\Viewer\Storage\FileStorage;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;

class FrontController
{
    /**
     * @return FileStorage
     */
    protected function getStorage()
    {
        return new FileStorage();
    }

    public function dispatch()
    {
        $storage = $this->getStorage();

        if (!empty($_REQUEST['dir'])) {
            $storage->setCurrentDirectory($_REQUEST['dir']);
        }

        if (isset($_GET['q'])) {
            $controller = new TypeAheadController();
        } else if (isset($_GET['callgraph'])) {
            $controller = new CallGraphController();
        } else if (isset($_GET['run'])) {
            $controller = new RunController();
        } else {
            $controller = new RunsListController();
        }

        $controller->setStorage($storage);
        CurrentPageHelper::setCurrentController($controller);
        $controller->indexAction();
    }
}
