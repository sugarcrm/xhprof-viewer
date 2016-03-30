<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\Run;

class RunController extends AbstractController
{
    protected $paramsList = array(
        'dir',
        'run',
        'symbol',
        'sort',
        'all',
        'list_url',
        'sql_sort_by',
        'sql_type',
        'sql_regex_text',
        'sql_regex_mod',
    );

    protected $paramDefaults = array(
        'symbol' => '',
        'sort' => 'wt',
        'all' => 0,
        'sql_sort_by' => 'time',
        'sql_type' => 'all',
        'sql_regex_text' => '',
        'sql_regex_mod' => 'i',
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

        global $run, $wts, $symbol, $sort, $run1, $run2, $source, $all, $source2, $sqlData, $elasticData;
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

        $params['dir'] = $this->storage->getCurrentDirectory();
        $params['list_url'] = $this->getParam('list_url');
        $params['sql_sort_by'] = $this->getParam('sql_sort_by');
        $params['sql_type'] = $this->getParam('sql_type');
        $params['sql_regex_text'] = $this->getParam('sql_regex_text');
        $params['sql_regex_mod'] = $this->getParam('sql_regex_mod');

        $GLOBALS['run_page_params'] = $params;

        $GLOBALS['vbar'] = ' class="vbar"';
        $GLOBALS['vwbar'] = ' class="vwbar"';
        $GLOBALS['vwlbar'] = ' class="vwlbar"';
        $GLOBALS['vbbar'] = ' class="vbbar"';
        $GLOBALS['vrbar'] = ' class="vrbar"';
        $GLOBALS['vgbar'] = ' class="vgbar"';

        $runData = $this->storage->getRunMetaData($run);
        $xhprofData = $this->storage->getRunXHProfData($run);
        $sqlData = $this->storage->getRunSqlData($run, array(
            'sort_by' => $params['sql_sort_by'],
            'type' => $params['sql_type'],
            'regex_text' => $params['sql_regex_text'],
            'regex_mod' => $params['sql_regex_mod'],
        ));
        $elasticData = $this->storage->getRunElasticData($run);

        Run::render($runData, $params, $xhprofData, $run, $symbol, $sort);
    }
}
