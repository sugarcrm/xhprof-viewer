<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\SqlPageTemplate;

class SqlController extends AbstractController
{
    protected $paramsList = array(
        'dir',
        'run',
        'page',
        'sort_by',
        'type',
        'regex_text',
        'regex_mod',
        'list_url',
    );

    protected $paramDefaults = array(
        'sort_by' => 'time',
        'type' => 'all',
        'regex_text' => '',
        'regex_mod' => 'i',
        'list_url' => '',
    );

    public function indexAction()
    {
        $runId = $this->getParam('run');
        $runData = $this->getStorage()->getRunMetaData($runId);
        $sqlData = $this->getStorage()->getRunSqlData($runId, array(
            'sort_by' => $this->getParam('sort_by'),
            'type' => $this->getParam('type'),
            'regex_text' => $this->getParam('regex_text'),
            'regex_mod' => $this->getParam('regex_mod'),
        ));

        SqlPageTemplate::render($runData, $sqlData);
    }
}
