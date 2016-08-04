<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\ElasticPageTemplate;

class ElasticController extends AbstractController
{
    protected $paramsList = array(
        'dir',
        'run',
        'page',
        'list_url',
    );

    protected $paramDefaults = array(
        'list_url' => '',
    );

    public function indexAction()
    {
        $runId = $this->getParam('run');
        $runData = $this->getStorage()->getRunMetaData($runId);
        $elasticData = $this->getStorage()->getRunElasticData($runId);

        ElasticPageTemplate::render($runData, $elasticData);
    }
}
