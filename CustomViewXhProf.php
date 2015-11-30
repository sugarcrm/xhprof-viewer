<?php


class CustomViewXhProf
{
    public function __construct()
    {
        $GLOBALS['dir'] = $this->getLogTo();
    }

    protected function getLogTo()
    {
        return $GLOBALS['profile_files_dir'];
    }

    protected function parseFilename($filename)
    {
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

        $pagination = [
            'offset' => 0,
            'limit' => 100,
        ];

        $filters = [
            'f_text' => '',
            'f_date_from' => date('m/d/Y', strtotime("-1 year")),
            'f_date_to' => date('m/d/Y'),
            'f_sort_by' => 'ts',
            'f_sort_dir' => 'asc',
        ];

        foreach ($pagination as $k => $v) {
            if (isset($_REQUEST[$k])) {
                $pagination[$k] = $_REQUEST[$k];
            }
        }
        foreach ($filters as $k => $v) {
            if (isset($_REQUEST[$k])) {
                $filters[$k] = $_REQUEST[$k];
            }
        }

        $total = 0;

        foreach (glob($this->getLogTo() . '/*') as $index => $file) {
            $pi = pathinfo($file);
            if ($pi['extension'] == 'xhprof') {
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
        $dFrom = date('Y-m-d', strtotime($filters['f_date_from']));
        $dTo = date('Y-m-d', strtotime($filters['f_date_to']));
        foreach ($bufFiles as $index => $file) {
            $fDate = date('Y-m-d', $file['timestamp']);
            if (!($fDate >= $dFrom && $fDate <= $dTo)) {
                unset($bufFiles[$index]);
            }
        }

        $searchText = trim($filters['f_text']);
        if (!empty($searchText)) {
            foreach ($bufFiles as $index => $file) {
                if (stripos($file['namespace'], $searchText) === false) {
                    unset($bufFiles[$index]);
                }
            }
        }

        $sortByMap = [
            'ts' => 'timestamp',
            'wt' => 'wall_time',
            'fs' => 'file_size',
            'sql' => 'sql_queries',
        ];
        $sortBy = $sortByMap[$filters['f_sort_by']];
        $sortAsc = $filters['f_sort_dir'] != 'desc';
        usort($bufFiles, function ($a, $b) use ($sortBy, $sortAsc) {
            return $sortAsc ? ($a[$sortBy] < $b[$sortBy]) : ($a[$sortBy] > $b[$sortBy]);
        });

        // apply pagination
        $limit = $pagination['limit'];
        $offset = $pagination['offset'];
        $start = $offset * $limit;
        if ($start > $total) {
            $start = $total;
            $offset = ceil($total / $limit);
        }

        $files = array_slice($bufFiles, $start, $limit, true);

        require(__DIR__ . '/XHProfList.php');
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

        global $run, $wts, $symbol, $sort, $run1, $run2, $source, $all, $source2;
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
        $params['module'] = 'Administration';
        $params['action'] = 'xhprof';

        echo "<html>";

        echo "<head><title>XHProf: Hierarchical Profiler Report</title>";
        xhprof_include_js_css($GLOBALS['base_path'] . '/xhprof');
        echo "</head>";

        echo "<body>";

        ?>
        <div>
            <?php if (!defined('XHPROF_ENTRY_POINT')) { ?>

            <?php } ?>
            <div style="text-align:center"><a href="?">List of profiler files</a></div>
            <div style="clear:both;">&nbsp;</div>
        </div>
        <?


        $GLOBALS['vbar'] = ' class="vbar"';
        $GLOBALS['vwbar'] = ' class="vwbar"';
        $GLOBALS['vwlbar'] = ' class="vwlbar"';
        $GLOBALS['vbbar'] = ' class="vbbar"';
        $GLOBALS['vrbar'] = ' class="vrbar"';
        $GLOBALS['vgbar'] = ' class="vgbar"';

        $xhprof_runs_impl = new XHProfRuns_IBM($this->getLogTo());

        $run_fname = $xhprof_runs_impl->file_name($run, $source);
        if ($this->hasSqlFile($run_fname)) {
            ?>
            <div style="border:1px solid silver;">
                <?
                $data = $GLOBALS['additional_data'] = unserialize(file_get_contents($run_fname . '.sql'));
                //print_r($data['backtrace_calls']);die();
                // prepare backtrace_calls
                if (isset($GLOBALS['additional_data']['backtrace_calls'])) {
                    $GLOBALS['additional_data']['backtrace_calls_prepared'] = array();
                    foreach ($GLOBALS['additional_data']['backtrace_calls'] as $k => $v) {
                        $GLOBALS['additional_data']['backtrace_calls_prepared'][str_replace('->', '::', $k)] = $v;
                    }
                }

                if (isset($_POST['sort_sql_by_hits'])) {
                    if ($_POST['sort_sql_by_hits']) {
                        $_SESSION['sort_sql_by_hits'] = 1;
                    } else {
                        unset($_SESSION['sort_sql_by_hits']);
                    }
                }
                if (isset($_POST['show_custom_data'])) {
                    if ($_POST['show_custom_data']) {
                        $_SESSION['show_custom_data'] = 1;
                    } else {
                        unset($_SESSION['show_custom_data']);
                    }
                }
                ?>
                <form method="post" action="?<?= $_SERVER['QUERY_STRING'] ?>">
                    <input type="hidden" value="0" name="show_custom_data"/>
                    <input type="checkbox" value="1"
                           name="show_custom_data" <?= isset($_SESSION['show_custom_data']) ? 'checked="checked"' : '' ?>
                           onclick="this.parentNode.submit();"/>
                    Show custom data
                </form>
                <?
                if (isset($_SESSION['show_custom_data']) && $_SESSION['show_custom_data']) {
                    ?>
                    <div>Custom data:
                        <pre><?= isset($data['app_data']) ? $this->printr($this->data2array($data['app_data'])) : 'n/a' ?></pre>
                    </div>
                    <?
                } ?>
                <div>SQL queries: <?= sizeof($data['sql']) ?></div>
                <div>SQL summary time: <?= $data['summary_time'] ?></div>
                <?if(!empty($data['summary_fetch_time'])) {?>
                    <div>DB fetch summary time: <?=$data['summary_fetch_time']?></div>
                <?}?>
                <a href="javascript:void(0)"
                   onclick="document.getElementById('d_sql').style.display=document.getElementById('d_sql').style.display=='none' ? 'block' : 'none';">See
                    all SQL queries</a>

                <div id="d_sql" style="border:1px solid silver;display:none;padding:5px;">

                    <form method="post" action="?<?= $_SERVER['QUERY_STRING'] ?>">
                        <input type="hidden" value="0" name="sort_sql_by_hits"/>
                        <input type="checkbox" value="1"
                               name="sort_sql_by_hits" <?= isset($_SESSION['sort_sql_by_hits']) ? 'checked="checked"' : '' ?>
                               onclick="this.parentNode.submit();"/>
                        Sort by Hits (unchecked - sorted by time)<br/>
                    </form>
                    <?
                    $dump_hash = array();
                    foreach ($data['sql'] as $row) {
                        $sqlKey = md5($row[0]);
                        $traceKey = md5($row[2]);
                        if (!isset($dump_hash[$sqlKey])) {
                            $dump_hash[$sqlKey] = array('time' => 0, 'hits' => 0, 'dumps' => array());
                        }

                        $dump_hash[$sqlKey]['hits']++;
                        $dump_hash[$sqlKey]['time'] += $row[1];
                        $dump_hash[$sqlKey]['sql'] = $row[0];
                        $dump_hash[$sqlKey]['fetch_count'] = isset($row[3]) ? $row[3] : 0;
                        $dump_hash[$sqlKey]['fetch_time'] = isset($row[4]) ? $row[4] : 0;

                        if (!isset($dump_hash[$sqlKey]['dumps'][$traceKey])) {
                            $dump_hash[$sqlKey]['dumps'][$traceKey] = array('hits' => 0, 'time' => 0);
                        }

                        $dump_hash[$sqlKey]['dumps'][$traceKey]['hits']++;
                        $dump_hash[$sqlKey]['dumps'][$traceKey]['time'] += $row[1];
                        $dump_hash[$sqlKey]['dumps'][$traceKey]['content'] = $row[2];
                        $dump_hash[$sqlKey]['dumps'][$traceKey]['fetch_time']+=$dump_hash[$sqlKey]['fetch_time'];
                    }
                    // sort dumps
                    function sortbytime($a, $b)
                    {
                        return $a['time'] < $b['time'];
                    }

                    function sortbyhits($a, $b)
                    {
                        return $a['hits'] < $b['hits'];
                    }

                    $sort_method = isset($_SESSION['sort_sql_by_hits']) ? 'sortbyhits' : 'sortbytime';
                    usort($dump_hash, $sort_method);
                    foreach ($dump_hash as $sql => &$sqlDumps) {
                        usort($sqlDumps['dumps'], $sort_method);
                    }


                    $ind = 0;
                    foreach ($dump_hash as $sql => $data) {
                        $ind++;
                        ?>
                        <div style="border:1px solid orange;margin:6px 3px;">
                            <div style="color:gray">
                                Hits: <span style="color:navy"><?= $data['hits'] ?></span>
                                Time: <span style="color:navy"><?= $data['time'] ?>s</span>
                                <? if(!empty($data['fetch_count'])) {
                                    $data['fetch_time'] = round($data['fetch_time'], 4);
                                    ?>
                                    FetchCount: <span style="color:navy"><?=$data['fetch_count']?></span>
                                    <? if ($data['fetch_time'] > 0) { ?>
                                        FetchTime: <span style="color:navy">~ <?=$data['fetch_time']?>s</span>
                                    <? } else { ?>
                                        <i>[fast]</i>
                                    <? } ?>
                                <? } ?>
                            </div>
                            <pre style="white-space:pre-line;"><?= $data['sql'] ?></pre>
                            <a href="javascript:void(0)"
                               onclick="document.getElementById('bt_cont<?= $ind ?>').style.display
                                   = document.getElementById('bt_cont<?= $ind ?>').style.display=='block' ? 'none' : 'block';"
                                ><?= sizeof($data['dumps']) ?> unique backtrace(s) for this query<a>
                                    <div style="display:none" id="bt_cont<?= $ind ?>">
                                        <? foreach ($data['dumps'] as $dd) {
                                            ?>
                                            <div style="border:1px solid silver;margin:3px;font-size:10px;color:#777;">
                                                <b style="color:#000">Hits: <?= $dd['hits'] ?>
                                                    <span style="margin-left:20px">[<?= $dd['time'] ?>s]</span>
                                                    <span
                                                        style="margin-left:20px"><?= round(($dd['time'] / $data['time']) * 100, 2) ?>
                                                        %</span>
                                                </b>
                                                <pre><?= $dd['content'] ?></pre>
                                            </div>
                                            <?
                                        } ?>
                                    </div>
                        </div>
                        <?
                    } ?>
                </div>
                <?php
                if (is_file($run_fname . '.elastic')):
                    $data = $this->getData($run_fname . '.elastic');
                    if (!empty($data['queries'])):
                        ?>
                        <script type="text/javascript">
                            var xhprofElastic = {
                                toggle: function (id) {
                                    document.getElementById(id).style.display
                                        = document.getElementById(id).style.display == 'block'
                                        ? 'none'
                                        : 'block';
                                }
                            }
                        </script>
                        <div style="border:1px solid silver;">
                            <div>Elastic queries: <?= sizeof($data['queries']) ?></div>
                            <div>Elastic summary time: <?= $data['summary_time'] ?></div>
                            <?php
                            foreach ($data['queries'] as $key => $query):
                                ?>
                                <a href="javascript:xhprofElastic.toggle('query_<?= $key ?>');">Show elastic query</a>
                                <div style="border:1px solid orange;margin:6px 3px;display: none;"
                                     id="query_<?= $key ?>">
                                    <p>Time: <?= $query->time ?></p>

                                    <p>Query:
                                    <pre id="query_body_<?= $key ?>"></pre>
                                    </p>
                                    <p>
                                        <a href="javascript:xhprofElastic.toggle('backtrace_<?= $key ?>');">
                                            Show backtrace
                                        </a>
                                    </p>

                                    <div id="backtrace_<?= $key ?>" style="display: none;">
                                            <pre>
                                                <?= $query->backtrace ?>
                                            </pre>
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    document.getElementById('query_body_<?=$key?>').innerHTML
                                        = JSON.stringify(<?= $query->query ?>, null, 2);
                                </script>
                                <?php
                            endforeach;
                            ?>
                        </div>
                        <?php
                    endif;
                endif;
                ?>
            </div>
            <?
        }
        displayXHProfReport($xhprof_runs_impl, $params, $source, $run, $wts,
            $symbol, $sort, $run1, $run2, $source2);
        echo "</body>";
        echo "</html>";
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

    function data2array(array $data, $l = 0)
    {
        $arr = array();
        if ($l < 8) {
            foreach ($data as $index => $row) {
                if (is_array($row) || ($row instanceof __PHP_Incomplete_Class)) {
                    $arr[$index] = $this->data2array((array)$row, $l + 1);
                } else {
                    $arr[$index] = $row;
                }
            }
        } else {
            $arr = '... too much recursion';
        }
        return $arr;
    }

    function printr($a, $l = 0)
    {
        if (!empty($a)) {
            $plus = '<span style="margin:1px 5px;border:1px solid #ccc;cursor:pointer;" onclick="expand_node(this);">+</span>';
            $head = '<div class="node" style="margin-left:' . (20) . 'px;border-left:1px solid #ccc;' . ($l ? 'display:none;' : '') . '" >';
            $out = '';
            if (is_array($a)) {
                $s = '';
                foreach ($a as $k => $v) {
                    if (is_array($v)) {
                        $buf = $this->printr($v, $l + 1);
                        $s .= '<div style="' . (!empty($buf) ? '' : 'color:#999;') . 'margin-left:' . (20) . 'px"> '
                            . (!empty($buf) ? $plus : '') . ($k . ': ') . '</div>' . $buf;
                    } else {
                        $s .= '<div style="margin-left:' . (20) . 'px">' . $k . ': ' . print_r($v, 1) . '</div>';
                    }
                }
                $out .= '<div style="margin-left:' . (20) . 'px">' . $s . '</div>';
            } else {
                $out .= '<div style="margin-left:' . (20) . 'px">' . (is_object($a) ? print_r($a, 1) : $a) . '</div>';
            }
            return $out ? $head . $out . '</div>' : '';
        } else {
            return '';
        }
    }

    protected function displayNotEnabled()
    {
        echo 'SugarXHprof is not enabled.';
    }
}
