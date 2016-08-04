<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Controllers;


use Sugarcrm\XHProf\Viewer\Templates\RunsListTemplate;

class RunsListController extends AbstractController
{
    protected $paramsList = array(
        'dir',
        'offset',
        'limit',
        'f_text',
        'f_date_from',
        'f_date_to',
        'f_sort_by',
        'f_sort_dir',
        'f_wt_min',
    );

    protected $paramDefaults = array(
        'offset' => 0,
        'limit' => 100,
        'f_text' => '',
        'f_sort_by' => 'ts',
        'f_sort_dir' => 'desc',
        'f_wt_min' => 300,
    );

    protected $sortByMap = array(
        'ts' => 'timestamp',
        'wt' => 'wall_time',
        'fs' => 'file_size',
        'sql' => 'sql_queries',
    );

    public function __construct()
    {
        $this->paramDefaults['f_date_from'] = date('m/d/Y', strtotime("-1 year"));
        $this->paramDefaults['f_date_to'] = date('m/d/Y');
    }

    public function indexAction()
    {
        if ($this->getParam('f_wt_min') == 120) {
            header('Location: ?dir=sugar');exit;
        }
        $limit = $this->getParam('limit');
        $page = $this->getParam('offset');

        // apply pagination
        $start = $page * $limit;

        $runs = $this->storage->getRunsList(array(
            'timestamp_from' => strtotime($this->getParam('f_date_from')),
            'timestamp_to' => strtotime($this->getParam('f_date_to')) + (60 * 60 * 24 - 1),
            'text' => $this->getParam('f_text'),
            'wall_time_min' => $this->getParam('f_wt_min'),
            'sort_by' => $this->sortByMap[$this->getParam('f_sort_by')],
            'sort_dir' => $this->getParam('f_sort_dir'),
            'limit' => $this->getParam('limit'),
            'offset' => $this->getParam('offset') * $limit
        ));

        if ($start > $runs['total']) {
            $start = $runs['total'];
            $page = ceil($runs['total'] / $limit);
        }

        RunsListTemplate::render($this->getStorage(), $limit, $runs, $start, $page);
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
}
