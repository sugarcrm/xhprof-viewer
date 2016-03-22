<?php

class CustomViewXhProf
{
    /**
     * @var \Sugarcrm\XHProf\Viewer\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var \Sugarcrm\XHProf\Viewer\Helpers\Version
     */
    protected $versionHelper;

    protected $pagination = array(
        'offset' => 0,
        'limit' => 100,
    );

    protected $filters;

    protected $sortByMap = array(
        'ts' => 'timestamp',
        'wt' => 'wall_time',
        'fs' => 'file_size',
        'sql' => 'sql_queries',
    );

    public function __construct()
    {
        $this->storage = new \Sugarcrm\XHProf\Viewer\Storage\FileStorage();
        $this->versionHelper = new \Sugarcrm\XHProf\Viewer\Helpers\Version();

        $this->filters = array(
            'f_text' => '',
            'f_date_from' => date('m/d/Y', strtotime("-1 year")),
            'f_date_to' => date('m/d/Y'),
            'f_sort_by' => 'ts',
            'f_sort_dir' => 'desc',
            'f_wt_min' => 300,
        );
    }

    protected function url($params) {
        return '?' . http_build_query($params);
    }

    protected function listUrl($params = array())
    {
        return $this->url(array_merge(
            array(
                'dir' => $this->storage->getCurrentDirectory(),
            ),
            $this->filters,
            $this->pagination,
            $params
        ));
    }

    protected function listFilterUrl($sortBy)
    {
        $dir = $this->filters['f_sort_by'] == $sortBy ?
            ($this->filters['f_sort_dir'] == 'desc' ? 'asc' : 'desc')
            : 'desc';

        return $this->listUrl(array(
            'f_sort_by' => $sortBy,
            'f_sort_dir' => $dir,
        ));
    }

    public function displayList()
    {
        foreach ($this->pagination as $k => $v) {
            if (isset($_REQUEST[$k])) {
                $this->pagination[$k] = $_REQUEST[$k];
            }
        }
        foreach ($this->filters as $k => $v) {
            if (isset($_REQUEST[$k])) {
                $this->filters[$k] = $_REQUEST[$k];
            }
        }

        $dFrom = date('Y-m-d', strtotime($this->filters['f_date_from']));
        $dTo = date('Y-m-d', strtotime($this->filters['f_date_to']));
        $searchText = trim($this->filters['f_text']);
        $minWT = (int) trim($this->filters['f_wt_min']);
        $sortBy = $this->sortByMap[$this->filters['f_sort_by']];
        $sortAsc = $this->filters['f_sort_dir'] != 'desc';
        $limit = $this->pagination['limit'];
        $page = $this->pagination['offset'];

        // apply pagination
        $start = $page * $limit;

        $runs = $this->storage->getRunsList(array(
            'date_from' => $dFrom,
            'date_to' => $dTo,
            'text' => $searchText,
            'wall_time_min' => $minWT,
            'sort_by' => $sortBy,
            'sort_dir' => $sortAsc,
            'limit' => $limit,
            'offset' => $start
        ));

        if ($start > $runs['total']) {
            $start = $runs['total'];
            $page = ceil($runs['total'] / $limit);
        }

        require(__DIR__ . '/xhprof/xhprof_lib/display/runs_list.php');
    }

    protected function displayRun()
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

        require (__DIR__ . '/xhprof/xhprof_lib/display/run.php');
    }

    protected function displayCallGraph()
    {
        ini_set('max_execution_time', 100);

        $params = array(// run id param
            'run' => array(XHPROF_STRING_PARAM, ''),

            // source/namespace/type of run
            'source' => array(XHPROF_STRING_PARAM, 'xhprof'),

            // the focus function, if it is set, only directly
            // parents/children functions of it will be shown.
            'func' => array(XHPROF_STRING_PARAM, ''),

            // image type, can be 'jpg', 'gif', 'ps', 'png'
            'type' => array(XHPROF_STRING_PARAM, 'png'),

            // only functions whose exclusive time over the total time
            // is larger than this threshold will be shown.
            // default is 0.01.
            'threshold' => array(XHPROF_FLOAT_PARAM, 0.01),

            // whether to show critical_path
            'critical' => array(XHPROF_BOOL_PARAM, true),

            // first run in diff mode.
            'run1' => array(XHPROF_STRING_PARAM, ''),

            // second run in diff mode.
            'run2' => array(XHPROF_STRING_PARAM, '')
        );

        // pull values of these params, and create named globals for each param
        xhprof_param_init($params);
        global $run, $source, $func, $type, $threshold, $critical, $run1, $run2, $xhprof_legal_image_types;

        // if invalid value specified for threshold, then use the default
        if ($threshold < 0 || $threshold > 1) {
            $threshold = $params['threshold'][1];
        }

        // if invalid value specified for type, use the default
        if (!array_key_exists($type, $xhprof_legal_image_types)) {
            $type = $params['type'][1]; // default image type.
        }

        if (!empty($run)) {
            // single run call graph image generation
            xhprof_render_image($this->storage, $run, $type,
                $threshold, $func, $source, $critical);
        }
    }

    protected function displayTypeAhead()
    {
        $params = array(
            'q'          => array(XHPROF_STRING_PARAM, ''),
            'run'        => array(XHPROF_STRING_PARAM, ''),
            'run1'       => array(XHPROF_STRING_PARAM, ''),
            'run2'       => array(XHPROF_STRING_PARAM, ''),
            'source'     => array(XHPROF_STRING_PARAM, 'xhprof'),
        );

        // pull values of these params, and create named globals for each param
        xhprof_param_init($params);

        global $q, $run;

        header('Content-Type: application/json');
        echo json_encode($this->storage->getRunXHprofMatchingFunctions($run, $q));
    }

    public function display()
    {
        header('Content-type: text/html; charset=utf-8');

        if (!empty($_REQUEST['dir'])) {
            $this->storage->setCurrentDirectory($_REQUEST['dir']);
        }

        if (isset($_GET['q'])) {
            $this->displayTypeAhead();
        } else if (isset($_GET['callgraph'])) {
            $this->displayCallGraph();
        } else if (isset($_GET['run']) || isset($_GET['run1'])) {
            $this->displayRun();
        } else {
            $this->displayList();
        }
    }

    protected function toTimePcs($s, $getmin = 1, $usekey = 'float')
    {
        $os = $s = intval($s);
        $l = array('seconds', 'minutes', 'hours', 'days');
        $fl = array(1, 60, 60 * 60, 60 * 60 * 24);
        $r = array('float' => array(), 'int' => array());
        for ($i = sizeof($l) - 1; $i >= 0; $i--) {
            $r['int'][$l[$i]] = floor($s / $fl[$i]);
            $s -= $r['int'][$l[$i]] * $fl[$i];
        }
        for ($i = sizeof($fl) - 1; $i >= 0; $i--) {
            if (($os / $fl[$i]) >= 1) {
                $r['float'][$l[$i]] = $os / $fl[$i];
            }
        }
        $rnd = (reset($r[$usekey]) / 10) >= 1 ? 0 : (reset($r[$usekey]) < 3 ? 2 : 1);
        $units = array_keys($r[$usekey]);
        return $getmin ? round(reset($r[$usekey]), $rnd) . ' ' . reset($units) : $r;
    }

    protected function toBytes($v)
    {
        $v = intval($v);
        $e = array(' bytes', 'KB', 'MB', 'GB', 'TB');
        $level = 0;
        while ($level < sizeof($e) && $v >= 1024) {
            $v = $v / 1024;
            $level++;
        }
        return ($level > 0 ? round($v, 2) : $v) . $e[$level];
    }
}
