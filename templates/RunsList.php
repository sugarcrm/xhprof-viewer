<?php

namespace Sugarcrm\XHProf\Viewer\Templates;

use \Sugarcrm\XHProf\Viewer\Controllers\RunsListController;
use \Sugarcrm\XHProf\Viewer\Templates\Common\Html\Head as HtmlHead;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\Url;

class RunsList
{
    protected static $controller;

    /**
     * @param mixed $controller
     */
    public static function setController($controller)
    {
        self::$controller = $controller;
    }

    public static function render(RunsListController $c, $limit, $runs, $start, $page)
    {
        ?><!DOCTYPE HTML><html>
        <?php HtmlHead::render(
        'List of profiler files - SugarCRM XHProf Viewer',
        array(
            'xhprof/css/xhprof.css',
            'bower_components/bootstrap/dist/css/bootstrap.min.css',
            'bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css',
            'bower_components/font-awesome/css/font-awesome.min.css',
        ),
        array(
            'bower_components/jquery/dist/jquery.min.js',
            'bower_components/bootstrap/dist/js/bootstrap.min.js',
            'bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
            'xhprof/js/list.js',
        ));
        ?>
        <body class="container-fluid">
        <form id="list-form" method="get" action="">
            <input type="hidden" id="offset_hidden" name="offset" value="<?php echo $c->getParam('offset') ?>" />
            <input type="hidden" id="f_sort_by" name="f_sort_by" value="<?php echo $c->getParam('f_sort_by') ?>" />
            <input type="hidden" id="f_sort_dir" name="f_sort_dir" value="<?php echo $c->getParam('f_sort_dir') ?>" />
            <div class="page-header form-inline" style="margin-top: 20px;">
                <a class="btn btn-primary pull-right" href="<?php echo Url::thisPageUrl($c) ?>"><i class="fa fa-refresh"></i> Refresh</a>
                <h1>SugarCRM XHProf Viewer <small>List of profiler files in
                        <select id="dir" name="dir" class="form-control">
                            <?php foreach ($c->getStorage()->listDirectories() as $dir => $dirName) { ?>
                                <option <?php if ($dir == $c->getStorage()->getCurrentDirectory()) { ?>selected<?php } ?>
                                        value="<?php echo htmlentities($dir) ?>"><?php echo htmlentities($dirName) ?></option>
                            <?php } ?>
                        </select>
                    </small></h1>
            </div>

            <div class="navbar-collapse collapse" id="searchbar" style="padding:0;margin-bottom:20px;">

                <div class="navbar-form navbar-right">
                    <label for="f_wt_min" style="padding-left:5px;">Min Wall Time</label>
                    <input style="width:104px" type="text" class="form-control" autocomplete="off"
                           placeholder="0" name="f_wt_min" id="f_wt_min" value="<?php echo htmlentities($c->getParam('f_wt_min')) ?>" />

                    <label for="date_1" style="padding-left:20px;">Date from</label>
                    <input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                           placeholder="Date From" name="f_date_from" id="date_1" value="<?php echo $c->getParam('f_date_from')?>" />

                    <label for="date_2">to</label>
                    <input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                           placeholder="Date To" name="f_date_to" id="date_2" value="<?php echo $c->getParam('f_date_to')?>" />

                    <button class="btn btn-default" style="margin-left:20px;" type="submit"><b>APPLY</b></button>
                </div>

                <div class="navbar-form" style="padding-left:0;">
                    <div class="form-group" style="display:inline;">
                        <div class="input-group" style="display:table;">
                            <span class="input-group-addon" style="width:1%;"><span class="glyphicon glyphicon-search"></span></span>
                            <input class="form-control" name="f_text" placeholder="Search Here" autocomplete="off" autofocus="autofocus" type="text"
                                   value="<?php echo htmlentities($c->getParam('f_text')) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <?php

            $filterValues = array(
                "f_date_from={$c->getParam('f_date_from')}",
                "f_date_to={$c->getParam('f_date_to')}",
                "f_text={$c->getParam('f_text')}",
            );
            $url = "?" . implode('&', $filterValues) . "&limit=$limit";


            $sortMarker = $c->getParam('f_sort_dir') == 'desc'
                ? '<span class="caret"></span>'
                : '<span class="dropup"><span class="caret"></span></span>';
            $url .= "&offset=0";

            ?>

            <table id="tbl" class="table table-condensed table-bordered">
                <thead>
                <tr class="active">
                    <th class="align-right">#</th>
                    <th>Profile</th>
                    <th class="align-right sortable"
                    ><a href="<?php echo static::thisPageFilterUrl('wt') ?>"
                        >Wall Time<?php echo $c->getParam('f_sort_by') == 'wt' ? $sortMarker : '' ?></a>
                    </th>
                    <th class="align-right sortable">
                        <a href="<?php echo static::thisPageFilterUrl('sql') ?>"
                        >SQL<?php echo $c->getParam('f_sort_by') == 'sql' ? $sortMarker : '' ?></a>
                    </th>
                    <th class="align-right sortable">
                        <a href="<?php echo static::thisPageFilterUrl('ts') ?>"
                        >Timestamp<?php echo $c->getParam('f_sort_by') == 'ts' ? $sortMarker : '' ?></a>
                    </th>
                    <th class="align-right">File Size</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $lastTestTime = 0;
                $microtime = microtime(1);
                foreach ($runs['runs'] as $index => $run) {
                    if ($c->getParam('f_sort_by') == 'ts' && (!$lastTestTime || abs($run['timestamp'] - $lastTestTime) > 5)) { ?>
                        <tr>
                            <td colspan="7" class="align-center age-bar">
                                <?php echo static::toTimePcs($microtime - $run['timestamp']); ?> ago
                            </td>
                        </tr>
                    <?php  }
                    $lastTestTime = $run['timestamp'];
                    ?>

                    <tr class="run">
                        <td class="align-right"><?php echo $index+1?></td>
                        <td><a title="<?php echo strlen($run['namespace']) > 100 ? $run['namespace'] : '' ?>"
                               href="<?php echo Url::url(array(
                                   'dir' => $c->getStorage()->getCurrentDirectory(),
                                   'run' => $run['run'],
                                   'source' => 'xhprof',
                                   'list_url' => Url::thisPageUrl()
                               )) ?>">
                                <?php  $name = substr($run['namespace'], 0, 100) . '' . (strlen($run['namespace']) > 100 ? ' ...' : '') ?>
                                <?php echo $name ? preg_replace("/" . preg_quote($c->getParam('f_text')) . "/i", "<b style='color:black;background-color:yellow;'>$0</b>", $name) : '-no-name-'?>
                            </a>
                        </td>
                        <td class="align-right"><?php echo number_format($run['wall_time'], 0, ' ', ' ')?></td>
                        <td class="align-right">
                            <?php echo $run['sql_queries'] === false ? '-' : $run['sql_queries'] ?>
                        </td>
                        <td class="align-right"><?php echo date('Y-m-d H:i:s', $run['timestamp'])?></td>
                        <td class="align-right"><?php echo static::toBytes($run['size'])?></td>
                    </tr>
                <?php  } ?>
                </tbody>
            </table>
            <div class="form-inline align-center">

                <?php
                if($runs['total'] > $limit) {
                    $pages = ceil($runs['total'] / $limit);
                    $startPage = ceil($start / $limit) + 1;
                    $startPage = $startPage ?: 1;
                    $pagesBefore = $startPage - 1;
                    $pagesAfter = $pages - $startPage;
                    if ($pagesBefore > 4) {
                        $pagesBefore = $pagesAfter < 4 ? 4 + (4 - $pagesAfter) : 4;
                    }
                    if (($pagesAfter + $pagesBefore) > 8) {
                        $pagesAfter = 8 - $pagesBefore;
                    }
                    ?>

                    <ul class="pagination pagination-sm">
                        <?php  $liClass = ($startPage > 1) ? '' : ' class="disabled"' ?>
                        <li<?php echo $liClass?>><a href="<?php echo Url::thisPageUrl(array('offset' => 0))?>">&lt;&lt;&lt; </a></li>
                        <li<?php echo $liClass?>><a href="<?php echo Url::thisPageUrl(array('offset' => $page-1))?>">&lt; </a></li>

                        <?php  for($i = 1; $i <= $pagesBefore; $i ++) { ?>
                            <li><a href="<?php echo Url::thisPageUrl(array('offset' => $startPage - ($pagesBefore - $i + 1) - 1))?>"><?php echo $startPage - ($pagesBefore - $i + 1)?></a></li>
                        <?php  } ?>

                        <li class="active"><a href="<?php echo Url::thisPageUrl(array('offset' => $startPage - 1))?>"><?php echo $startPage?></a></li>

                        <?php  for($i = 1; $i <= $pagesAfter; $i ++) { ?>
                            <li><a href="<?php echo Url::thisPageUrl(array('offset' => $i + $startPage - 1))?>"><?php echo $i + $startPage?></a></li>
                        <?php  } ?>

                        <?php  $liClass = ($pagesAfter > 0) ? '' : ' class="disabled"' ?>
                        <li<?php echo $liClass?>><a href="<?php echo Url::thisPageUrl(array('offset' => $page+1))?>"> &gt;</a></li>
                        <li<?php echo $liClass?>><a href="<?php echo Url::thisPageUrl(array('offset' => $pages-1))?>"> &gt;&gt;&gt;</a></li>
                    </ul>
                    <p>Filtered: <?php echo $runs['total']?> / Total: <?php echo $runs['grand_total'] ?> / <?php echo $pages?> pages</p>
                    <?php
                } else { ?>
                    <p>Filtered: <?php echo $runs['total']?> / Total: <?php echo $runs['grand_total'] ?></p>
                <?php  } ?>

                <p>
                    Results per page:
                    <select class="form-control" id="limit" name="limit">
                        <?php  foreach (array(10, 50, 100, 500, 1000, 10000, 100000) as $val) { ?>
                            <option value="<?php echo $val?>"<?php echo $val == $limit ? ' selected' : ''?>><?php echo $val?></option>
                        <?php  } ?>
                    </select>
                </p>
                <?php \Sugarcrm\XHProf\Viewer\Templates\RunsList\Version::render(); ?>
            </div>
        </form>
        <script type="application/javascript">
            $(function () {
                $('[data-toggle="tooltip"]').tooltip({html: true})
            });
        </script>
        </body>
        </html>

        <?php
    }

    protected static function thisPageFilterUrl($sortBy)
    {
        $controller = Url::getCurrentController();

        $dir = $controller->getParam('f_sort_by') == $sortBy ?
            ($controller->getParam('f_sort_dir') == 'desc' ? 'asc' : 'desc')
            : 'desc';

        return Url::thisPageUrl(array(
            'f_sort_by' => $sortBy,
            'f_sort_dir' => $dir,
        ));
    }

    protected static function toTimePcs($s, $getmin = 1, $usekey = 'float')
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
