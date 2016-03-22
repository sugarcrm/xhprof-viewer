<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


class TypeAheadController extends AbstractController
{
    /**
     * Return suggested symbols based on the query $q
     *
     * @param $run string run id against which to search
     * @param $q string query string
     */
    public function indexAction()
    {
        header('Content-Type: application/json');
        echo json_encode($this->storage->getRunXHprofMatchingFunctions($_GET['run'], $_GET['q']));
    }
}
