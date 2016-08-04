<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

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
        } elseif (isset($_GET['callgraph'])) {
            $controller = new CallGraphController();
        } elseif (isset($_GET['run'])) {
            if (isset($_GET['page'])) {
                switch ($_GET['page']) {
                    case 'sql':
                        $controller = new SqlController();
                        break;
                    case 'elastic':
                        $controller = new ElasticController();
                        break;
                    default:
                        throw new \RuntimeException('Unknown page: ' . $_GET['page']);
                }
            } else {
                $controller = new RunController();
            }
        } else {
            $controller = new RunsListController();
        }

        $controller->setStorage($storage);
        CurrentPageHelper::setCurrentController($controller);
        $controller->indexAction();
    }
}
