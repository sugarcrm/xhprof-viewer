<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\Run;

class RunController extends AbstractController
{
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
        $params['list_url'] = !empty($_REQUEST['list_url']) ? $_REQUEST['list_url'] : '';
        $params['sql_sort_by'] = !empty($_REQUEST['sql_sort_by']) ? $_REQUEST['sql_sort_by'] : 'time';
        $params['sql_type'] = !empty($_REQUEST['sql_type']) ? $_REQUEST['sql_type'] : 'all';
        $params['sql_regex_text'] = !empty($_REQUEST['sql_regex_text']) ? $_REQUEST['sql_regex_text'] : '';
        $params['sql_regex_mod'] = !empty($_REQUEST['sql_regex_mod']) ? $_REQUEST['sql_regex_mod'] : '';

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
