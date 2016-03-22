<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


class RunsListController extends AbstractController
{
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
    }

    public function indexAction()
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

        require 'xhprof/xhprof_lib/display/runs_list.php';
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
