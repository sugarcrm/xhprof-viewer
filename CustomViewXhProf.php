<?php


class CustomViewXhProf
{
    protected $availableSubDirs = array('');

    protected $currentSubDir = '';

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
        $this->filters = array(
            'f_text' => '',
            'f_date_from' => date('m/d/Y', strtotime("-1 year")),
            'f_date_to' => date('m/d/Y'),
            'f_sort_by' => 'ts',
            'f_sort_dir' => 'desc',
            'f_wt_min' => 300,
        );

        $GLOBALS['dir'] = $this->getLogTo();
    }

    protected function getLogTo()
    {
        return $GLOBALS['profile_files_dir'] . (!empty($this->currentSubDir) ? '/' . $this->currentSubDir : '');
    }

    protected function url($params) {
        return '?' . http_build_query($params);
    }

    protected function listUrl($params = array())
    {
        return $this->url(array_merge(
            array(
                'dir' => $this->currentSubDir,
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

    protected function parseFilename($filename)
    {
        if (preg_match('/^(\d+)d(\d+)d(\d+)d(\d+)d(\d+)\.([^.]+)$/', $filename, $matches)) {
            return array(
                'shortname' => '',
                'timestamp' => floatval($matches[1] . '.' . $matches[2]),
                'wall_time' => $matches[3],
                'sql_queries' => $matches[4],
                'elastic_queries' => $matches[5],
                'namespace' => $matches[6]
            );
        }

        // Check of OOTB Sugar file format
        if (preg_match('/^([A-Za-z0-9]+)\.(\d+-\d+)-(.*)$/', $filename, $matches)) {
            return array(
                'shortname' => '',
                'timestamp' => floatval(str_replace('-', '.', $matches[2])),
                'wall_time' => 0,
                'sql_queries' => 0,
                'namespace' => $matches[3]
            );
        }

        $name_parts = explode('.', $filename);
        $shortname = array_shift($name_parts);
        $shortname .= '.' . array_shift($name_parts);
        $slq_queries = array_shift($name_parts);
        $wt = array_shift($name_parts);
        $namespace = implode('_', $name_parts);
        $buf = array(
            'shortname' => $shortname,
            'timestamp' => floatval($shortname),
            'wall_time' => $wt,
            'sql_queries' => intval($slq_queries),
            'namespace' => $namespace
        );

        return $buf;
    }

    public function displayList()
    {
        $bufFiles = array();

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

        $total = 0;

        foreach (glob($this->getLogTo() . '/*') as $index => $file) {
            $pi = pathinfo($file);
            if (!empty($pi['extension']) && $pi['extension'] == 'xhprof') {
                $buf = $this->parseFilename($pi['filename']);

                $stat = stat($file);
                $buf['stat'] = $stat;
                $buf['file_size'] = $stat['size'];
                $buf['pi'] = $pi;
                $buf['file'] = str_replace($this->getLogTo() . '/', '', $file);
                $buf['path'] = $file;

                $bufFiles[] = $buf;

                $total++;
            }
        }

        // apply filters
        // filter by date
        $dFrom = date('Y-m-d', strtotime($this->filters['f_date_from']));
        $dTo = date('Y-m-d', strtotime($this->filters['f_date_to']));
        foreach ($bufFiles as $index => $file) {
            $fDate = date('Y-m-d', $file['timestamp']);
            if (!($fDate >= $dFrom && $fDate <= $dTo)) {
                unset($bufFiles[$index]);
            }
        }

        $searchText = trim($this->filters['f_text']);
        if (!empty($searchText)) {
            foreach ($bufFiles as $index => $file) {
                if (stripos($file['namespace'], $searchText) === false) {
                    unset($bufFiles[$index]);
                }
            }
        }

        $minWT = (int) trim($this->filters['f_wt_min']);
        if (!empty($minWT)) {
            foreach ($bufFiles as $index => $file) {
                if ($file['wall_time'] < $minWT * 1E3) {
                    unset($bufFiles[$index]);
                }
            }
        }

        $sortBy = $this->sortByMap[$this->filters['f_sort_by']];
        $sortAsc = $this->filters['f_sort_dir'] != 'desc';
        usort($bufFiles, function ($a, $b) use ($sortBy, $sortAsc) {
            return $sortAsc ? ($a[$sortBy] > $b[$sortBy]) : ($a[$sortBy] < $b[$sortBy]);
        });

        $total = count($bufFiles);

        // apply pagination
        $limit = $this->pagination['limit'];
        $offset = $this->pagination['offset'];
        $start = $offset * $limit;
        if ($start > $total) {
            $start = $total;
            $offset = ceil($total / $limit);
        }

        $files = array_slice($bufFiles, $start, $limit, true);

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

        global $run, $wts, $symbol, $sort, $run1, $run2, $source, $all, $source2,
               $sqlCount, $sqlTime, $sqlFetchTime, $sqlData, $elasticCount, $elasticTime, $elasticData;
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

        $params['dir'] = $this->currentSubDir;
        $params['list_url'] = !empty($_REQUEST['list_url']) ? $_REQUEST['list_url'] : '';
        $params['sql_sort_by'] = !empty($_REQUEST['sql_sort_by']) ? $_REQUEST['sql_sort_by'] : 'time';
        $params['sql_type'] = !empty($_REQUEST['sql_type']) ? $_REQUEST['sql_type'] : 'all';
        $params['sql_regex_text'] = !empty($_REQUEST['sql_regex_text']) ? $_REQUEST['sql_regex_text'] : '';
        $params['sql_regex_mod'] = !empty($_REQUEST['sql_regex_mod']) ? $_REQUEST['sql_regex_mod'] : '';

        $GLOBALS['run_page_params'] = $params;

        $runData = $this->parseFilename($run);

        $GLOBALS['vbar'] = ' class="vbar"';
        $GLOBALS['vwbar'] = ' class="vwbar"';
        $GLOBALS['vwlbar'] = ' class="vwlbar"';
        $GLOBALS['vbbar'] = ' class="vbbar"';
        $GLOBALS['vrbar'] = ' class="vrbar"';
        $GLOBALS['vgbar'] = ' class="vgbar"';

        $xhprof_runs_impl = new XHProfRuns_IBM($this->getLogTo());

        $run_fname = $xhprof_runs_impl->file_name($run, $source);
        $sqlCount = $sqlTime = $sqlFetchTime = 0;
        if ($this->hasSqlFile($run_fname)) {
            $data = $GLOBALS['additional_data'] = unserialize(file_get_contents($run_fname . '.sql'));

            //print_r($data['backtrace_calls']);die();
            // prepare backtrace_calls
            if (isset($GLOBALS['additional_data']['backtrace_calls'])) {
                $GLOBALS['additional_data']['backtrace_calls_prepared'] = array();
                foreach ($GLOBALS['additional_data']['backtrace_calls'] as $k => $v) {
                    $GLOBALS['additional_data']['backtrace_calls_prepared'][str_replace('->', '::', $k)] = $v;
                }
            }

            $sqlCount = sizeof($data['sql']);
            $sqlTime = $data['summary_time'];
            if (!empty($data['summary_fetch_time'])) {
                $sqlFetchTime = $data['summary_fetch_time'];
            }

            $sqlTypeRegexMap = array(
                'select' => '/^\w*select/i',
                'modify' => '/^\w*(insert|update)/i'
            );

            $sqlData = $dump_hash = array();
            foreach ($data['sql'] as $row) {

                $sqlType = 'other';
                foreach ($sqlTypeRegexMap as $type => $regex) {
                    if (preg_match($regex, $row[0])) {
                        $sqlType = $type;
                    }
                }

                if ($params['sql_type'] != 'all' && $params['sql_type'] != $sqlType) {
                    continue;
                }

                $matches = array();
                if ($params['sql_regex_text']) {
                    if (preg_match_all(
                        '/' . $params['sql_regex_text'] . '/' . $params['sql_regex_mod'],
                        $row[0],
                        $matches,
                        PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE
                    )) {
                        $matches = array_map(function($match) {
                            return array($match[1], $match[1] + strlen($match[0]));
                        }, $matches[0]);
                    } else {
                        continue;
                    }
                }

                $sqlKey = md5($row[0]);
                $traceKey = md5($row[2]);
                if (!isset($dump_hash[$sqlKey])) {
                    $dump_hash[$sqlKey] = array('time' => 0, 'hits' => 0, 'dumps' => array());
                }

                $dump_hash[$sqlKey]['hits']++;
                $dump_hash[$sqlKey]['time'] += $row[1];
                $dump_hash[$sqlKey]['query'] = $row[0];
                $dump_hash[$sqlKey]['fetch_count'] = isset($row[3]) ? $row[3] : 0;
                $dump_hash[$sqlKey]['fetch_time'] = isset($row[4]) ? $row[4] : 0;
                $dump_hash[$sqlKey]['highlight_positions'] = $matches;

                if (!isset($dump_hash[$sqlKey]['dumps'][$traceKey])) {
                    $dump_hash[$sqlKey]['dumps'][$traceKey] = array('hits' => 0, 'time' => 0);
                }

                $dump_hash[$sqlKey]['dumps'][$traceKey]['hits']++;
                $dump_hash[$sqlKey]['dumps'][$traceKey]['time'] += $row[1];
                $dump_hash[$sqlKey]['dumps'][$traceKey]['content'] = htmlspecialchars($row[2]);
                $dump_hash[$sqlKey]['dumps'][$traceKey]['content_short'] = htmlspecialchars($this->shortenStackTrace($row[2]));
//                        $dump_hash[$sqlKey]['dumps'][$traceKey]['fetch_time']+= isset($dump_hash[$sqlKey]['fetch_time']) ? $dump_hash[$sqlKey]['fetch_time'] : 0;
            }

            $sortCallback = function($a, $b) use ($params) {
                return $a[$params['sql_sort_by']] < $b[$params['sql_sort_by']];
            };

            usort($dump_hash, $sortCallback);
            foreach ($dump_hash as $sql => &$sqlDumps) {
                usort($sqlDumps['dumps'], $sortCallback);
            }

            $sqlData = $dump_hash;
        }

        $elasticCount = $elasticTime = 0;
        $elasticData = array();
        if (is_file($run_fname . '.elastic')) {
            $data = $this->getData($run_fname . '.elastic');
            $elasticCount = sizeof($data['queries']);
            $queries = array();
            foreach ($data['queries'] as $query) {
                $queries[] = array(
                    'query' => array($query[0], $query[1]),
                    'hits' => 1,
                    'time' => $query[2],
                    'dumps' => array(
                        array(
                            'hits' => 1,
                            'time' => $query[2],
                            'content' => $query[3],
                            'content_short' => $this->shortenStackTrace($query[3])
                        ),
                    )
                );
            }
            $data['queries'] = $queries;
            $elasticData = $queries;
            $elasticTime = $data['summary_time'];
        }

        require (__DIR__ . '/xhprof/xhprof_lib/display/run.php');
    }

    protected function shortenStackTrace($trace)
    {
        return preg_replace('/^(#\d+).*\[Line: (\d+|n\/a)\]/m', '$1' ,$trace);
    }

    /**
     * Gets unserialized data from fileName
     * @param $fileName
     * @return mixed
     */
    protected function getData($fileName)
    {
        return unserialize(file_get_contents($fileName));
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

        $xhprof_runs_impl = new XHProfRuns_IBM($this->getLogTo());

        if (!empty($run)) {
            // single run call graph image generation
            xhprof_render_image($xhprof_runs_impl, $run, $type,
                $threshold, $func, $source, $critical);
        } else {
            // diff report call graph image generation
            xhprof_render_diff_image($xhprof_runs_impl, $run1, $run2,
                $type, $threshold, $source);
        }
    }

    protected function displayTypeAhead()
    {
        $xhprof_runs_impl = new XHProfRuns_IBM($this->getLogTo());
        include_once $GLOBALS['XHPProfLibRoot'] . '/display/typeahead_common.php';
    }

    public function display()
    {
        $this->prepareSubdirs();

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

    function toTimePcs($s, $getmin = 1, $usekey = 'float')
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
        return $getmin ? round(reset($r[$usekey]), $rnd) . ' ' . reset(array_keys($r[$usekey])) : $r;
    }

    function toBytes($v)
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

    function hasSqlFile($file)
    {
        return is_file($file . '.sql');
    }

    protected function prepareSubdirs()
    {
        $root = $GLOBALS['profile_files_dir'];

        $dirs = array_map(function($item) {
            return basename($item);
        }, glob($root . '/*', GLOB_ONLYDIR));

        array_unshift($dirs, "");

        $this->availableSubDirs = $dirs;

        if (!empty($_REQUEST['dir']) && in_array($_REQUEST['dir'], $dirs)) {
            $this->currentSubDir = $_REQUEST['dir'];
        }
    }
}
