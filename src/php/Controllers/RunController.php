<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\RunTemplate;

class RunController extends AbstractController
{
    protected $paramsList = array(
        'dir',
        'run',
        'symbol',
        'sort',
        'all',
        'list_url',
    );

    protected $paramDefaults = array(
        'symbol' => '',
        'sort' => 'wt',
        'all' => 0,
        'list_url' => '',
    );

    public function indexAction()
    {
        // param name, its type, and default value
        $params = array(
            'run' => array(XHPROF_STRING_PARAM, ''),
            'wts' => array(XHPROF_STRING_PARAM, ''),
            'symbol' => array(XHPROF_STRING_PARAM, ''),
            'sort' => array(XHPROF_STRING_PARAM, 'wt'), // wall time
            'run1' => array(XHPROF_STRING_PARAM, ''),
            'run2' => array(XHPROF_STRING_PARAM, ''),
            'source' => array(XHPROF_STRING_PARAM, 'xhprof'),
            'all' => array(XHPROF_UINT_PARAM, 0),
            'source2' => array(XHPROF_STRING_PARAM, 'xhprof'),
        );

        // pull values of these params, and create named globals for each param
        xhprof_param_init($params);

        global $run, $wts, $symbol, $sort, $run1, $run2, $source, $all, $source2, $sort_col;
        /* reset params to be a array of variable names to values
           by the end of this page, param should only contain values that need
           to be preserved for the next page. unset all unwanted keys in $params.
         */
        foreach ($params as $k => $v) {
            $params[$k] = $$k;

            // unset key from params that are using default values. So URLs aren't
            // ridiculously long.
            if ($params[$k] == $v[1]) {
                unset($params[$k]);
            }
        }

        $sort_col = $this->getParam('sort');
        $params['dir'] = $this->storage->getCurrentDirectory();
        $params['list_url'] = $this->getParam('list_url');

        $GLOBALS['run_page_params'] = $params;

        $runData = $this->storage->getRunMetaData($run);
        $xhprofData = $this->storage->getRunXHProfData($run);
        RunTemplate::render($runData, $params, $xhprofData, $symbol);
    }
}
