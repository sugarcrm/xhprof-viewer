<?php /** @var CustomViewXhProf $this  */?><html>
<head>
    <title>XHProf: Hierarchical Profiler Report</title>
    <link href='xhprof/css/xhprof.css' rel='stylesheet' type='text/css' />
    <link href='bower_components/bootstrap/dist/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
    <link href='bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css' rel='stylesheet' type='text/css' />
    <link href='bower_components/font-awesome/css/font-awesome.min.css' rel='stylesheet' type='text/css' />
    <script src='bower_components/jquery/dist/jquery.min.js'></script>
    <script src='bower_components/bootstrap/dist/js/bootstrap.min.js'></script>
</head>
<body class="container-fluid">
    <form id="list-form" method="get" action="">
        <input type="hidden" id="offset_hidden" name="offset" value="<?php echo $this->pagination['offset'] ?>" />
        <input type="hidden" id="f_sort_by" name="f_sort_by" value="<?php echo $this->filters['f_sort_by'] ?>" />
        <input type="hidden" id="f_sort_dir" name="f_sort_dir" value="<?php echo $this->filters['f_sort_dir'] ?>" />
        <div class="page-header form-inline" style="margin-top: 20px;">
            <a class="btn btn-primary pull-right" href="<?php echo $this->listUrl() ?>"><i class="fa fa-refresh"></i> Refresh</a>
            <h1>SugarCRM XHProf Viewer <small>List of profiler files in
                    <select id="dir" name="dir" class="form-control">
                        <?php foreach ($this->availableSubDirs as $dir) { ?>
                            <option <?php if ($dir == $this->currentSubDir) { ?>selected<?php } ?>
                                    value="<?php echo $dir ?>">/<?php echo $dir ?></option>
                        <?php } ?>
                    </select>
                </small></h1>
        </div>

        <div class="navbar-collapse collapse" id="searchbar" style="padding:0;margin-bottom:20px;">

            <div class="navbar-form navbar-right">
                <label for="f_wt_min" style="padding-left:5px;">Min Wall Time</label>
                <input style="width:104px" type="text" class="form-control" autocomplete="off"
                       placeholder="0" name="f_wt_min" id="f_wt_min" value="<?php echo htmlentities($this->filters['f_wt_min']) ?>" />

                <label for="date_1" style="padding-left:20px;">Date from</label>
                <input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                       placeholder="Date From" name="f_date_from" id="date_1" value="<?php echo $this->filters['f_date_from']?>" />

                <label for="date_2">to</label>
                <input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                       placeholder="Date To" name="f_date_to" id="date_2" value="<?php echo $this->filters['f_date_to']?>" />

                <button class="btn btn-default" style="margin-left:20px;" type="submit"><b>APPLY</b></button>
            </div>

            <div class="navbar-form" style="padding-left:0;">
                <div class="form-group" style="display:inline;">
                    <div class="input-group" style="display:table;">
                        <span class="input-group-addon" style="width:1%;"><span class="glyphicon glyphicon-search"></span></span>
                        <input class="form-control" name="f_text" placeholder="Search Here" autocomplete="off" autofocus="autofocus" type="text"
                               value="<?php echo htmlentities($this->filters['f_text']) ?>">
                    </div>
                </div>
            </div>
        </div>

    <!--<div class="row">-->
    <!--    <div class="col-md-8">-->
    <!--        <nav class="navbar navbar-default" role="navigation">-->
    <!--            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">-->
    <!--                <form action="" method="get">-->
    <!--                    <input type="hidden" name="offset" value="0" />-->
    <!--                    <input type="hidden" name="f_sort_by" value="--><?php //=$filters['f_sort_by']?><!--" />-->
    <!--                    <input type="hidden" name="f_sort_dir" value="--><?php //=$filters['f_sort_dir']?><!--" />-->
    <!--                    <ul class="nav navbar-nav">-->
    <!--                        <li><a href="#">Date from</a></li>-->
    <!--                        <li class=" navbar-form"><input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"-->
    <!--                                                        placeholder="Date From" name="f_date_from" id="date_1" value="--><?php //=$filters['f_date_from']?><!--"></li>-->
    <!--                        <li><a href="#">to</a></li>-->
    <!--                        <li class=" navbar-form"><input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"-->
    <!--                                                        placeholder="Date To" name="f_date_to" id="date_2" value="--><?php //=$filters['f_date_to']?><!--"></li>-->
    <!--                        <li>-->
    <!---->
    <!--                        </li>-->
    <!--                        <li class=" navbar-form">-->
    <!---->
    <!--                        </li>-->
    <!--                        <li class=" navbar-form">-->
    <!--                            <button class="btn btn-default" type="submit"><b>APLLY</b></button>-->
    <!--                        </li>-->
    <!--                    </ul>-->
    <!--                    <div class="col-sm-3 col-md-3 pull-right navbar-form">-->
    <!--                        <div class="input-group">-->
    <!--                            <input type="text" class="form-control" placeholder="Search" name="f_text" value="--><?php //=$filters['f_text']?><!--">-->
    <!--                            <div class="input-group-btn">-->
    <!--                                <button class="btn btn-default" type="submit"><b>APLLY</b></button>-->
    <!--                            </div>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                </form>-->
    <!--            </div>-->
    <!--        </nav>-->
    <!--    </div>-->
    <!---->
    <!--    <div class="col-md-4">-->
    <!---->
    <!--    </div>-->
    <!--</div>-->

    <!--<div style="clear:both">-->
    <!--    <button id="b_cd" class="btn btn-default" disabled="disabled" onclick="checkdiff()" >Check difference between 2 files</button>-->
    <!--</div>-->

    <?php

    $filterValues = [
        "f_date_from={$this->filters['f_date_from']}",
        "f_date_to={$this->filters['f_date_to']}",
        "f_text={$this->filters['f_text']}",
    ];
    $url = "?" . implode('&', $filterValues) . "&limit=$limit";


    $sortMarker = $this->filters['f_sort_dir'] == 'desc'
        ? '<span class="caret"></span>'
        : '<span class="dropup"><span class="caret"></span></span>';
    $url .= "&offset=0";

    ?>

    <table id="tbl" class="table table-condensed table-bordered">
        <thead>
        <tr class="active">
    <!--        <th>-->
    <!--            <div data-toggle="tooltip" title="Compare two runs.<br/>Select two runs in the list and press this button to compare them.">-->
    <!--                <a id="b_cd" class="btn btn-primary btn-compare btn-sm"><i class="fa fa-balance-scale"></i></a>-->
    <!--            </div>-->
    <!--        </th>-->
            <th class="align-right">#</th>
            <th>Profile</th>
            <th class="align-right sortable"
                ><a href="<?php echo  $this->listFilterUrl('wt') ?>"
                    >Wall Time<?php echo $this->filters['f_sort_by'] == 'wt' ? $sortMarker : '' ?></a>
            </th>
            <th class="align-right sortable">
                <a href="<?php echo  $this->listFilterUrl('sql') ?>"
                    >SQL<?php echo $this->filters['f_sort_by'] == 'sql' ? $sortMarker : '' ?></a>
            </th>
            <th class="align-right sortable">
                <a href="<?php echo  $this->listFilterUrl('ts') ?>"
                    >Timestamp<?php echo $this->filters['f_sort_by'] == 'ts' ? $sortMarker : '' ?></a>
            </th>
            <th class="align-right sortable">
                <a href="<?php echo  $this->listFilterUrl('fs') ?>"
                    >File Size<?php echo $this->filters['f_sort_by'] == 'fs' ? $sortMarker : '' ?></a>
            </th>
        </tr>
        </thead>
        <tbody>

        <?php
        $lastTestTime = 0;
        $microtime = microtime(1);
        foreach ($files as $index => $file) {
            if ($this->filters['f_sort_by'] == 'ts' && (!$lastTestTime || abs($file['timestamp'] - $lastTestTime) > 5)) { ?>
                <tr>
                    <td colspan="7" class="align-center age-bar">
                        <?php echo  $this->toTimePcs($microtime-$file['stat']['mtime']); ?> ago
                    </td>
                </tr>
            <?php  }
            $lastTestTime = $file['timestamp'];
            ?>

            <tr class="run">
    <!--            <td class="align-center"><input type="checkbox" data-run-name="--><?php //=urlencode($file['pi']['filename'])?><!--"></td>-->
                <td class="align-right"><?php echo $index+1?></td>
                <td><a title="<?php echo strlen($file['namespace']) > 100 ? $file['namespace'] : '' ?>"
                       href="<?php echo $this->url(array(
                           'dir' => $this->currentSubDir,
                           'run' => $file['pi']['filename'],
                           'source' => $file['pi']['extension'],
                           'list_url' => $this->listUrl()
                       )) ?>">
                        <?php  $name = substr($file['namespace'], 0, 100) . '' . (strlen($file['namespace']) > 100 ? ' ...' : '') ?>
                        <?php echo $name ? preg_replace("/" . preg_quote($searchText) . "/i", "<b style='color:black;background-color:yellow;'>$0</b>", $name) : '-no-name-'?>
                    </a>
                </td>
                <td class="align-right"><?php echo number_format($file['wall_time'], 0, ' ', ' ')?></td>
                <td class="align-right">
                    <?php if ($this->hasSqlFile($file['path'])) { ?>
                        <?php echo $file['sql_queries']?>
                    <?php  } else { ?>
                        <?php echo $file['sql_queries']?> (no file)
                    <?php  } ?>
                </td>
                <td class="align-right"><?php echo date('Y-m-d H:i:s', $file['timestamp'])?></td>
                <td class="align-right"><?php echo $this->toBytes($file['stat']['size'])?></td>
            </tr>
        <?php  } ?>
        </tbody>
    </table>
        <div class="form-inline align-center">

            <?php
            if($total > $limit) {
                $pages = ceil($total / $limit);
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
                    <li<?php echo $liClass?>><a href="<?php echo $this->listUrl(array('offset' => 0))?>">&lt;&lt;&lt; </a></li>
                    <li<?php echo $liClass?>><a href="<?php echo $this->listUrl(array('offset' => $offset-1))?>">&lt; </a></li>

                    <?php  for($i = 1; $i <= $pagesBefore; $i ++) { ?>
                        <li><a href="<?php echo $this->listUrl(array('offset' => $startPage - ($pagesBefore - $i + 1) - 1))?>"><?php echo $startPage - ($pagesBefore - $i + 1)?></a></li>
                    <?php  } ?>

                    <li class="active"><a href="<?php echo $this->listUrl(array('offset' => $startPage - 1))?>"><?php echo $startPage?></a></li>

                    <?php  for($i = 1; $i <= $pagesAfter; $i ++) { ?>
                        <li><a href="<?php echo $this->listUrl(array('offset' => $i + $startPage - 1))?>"><?php echo $i + $startPage?></a></li>
                    <?php  } ?>

                    <?php  $liClass = ($pagesAfter > 0) ? '' : ' class="disabled"' ?>
                    <li<?php echo $liClass?>><a href="<?php echo $this->listUrl(array('offset' => $offset+1))?>"> &gt;</a></li>
                    <li<?php echo $liClass?>><a href="<?php echo $this->listUrl(array('offset' => $pages-1))?>"> &gt;&gt;&gt;</a></li>
                </ul>
                <p>Results total: <?php echo $total?> / <?php echo $pages?> pages</p>
                <?php
            } else { ?>
                <p>Results total: <?php echo $total?></p>
            <?php  } ?>

            <p>
                Results per page:
                <select class="form-control" id="limit" name="limit">
                    <?php  foreach ([10, 50, 100, 500, 1000, 10000, 100000] as $val) { ?>
                        <option value="<?php echo $val?>"<?php echo $val == $limit ? ' selected' : ''?>><?php echo $val?></option>
                    <?php  } ?>
                </select>
            </p>


        </div>

    </form>

    <script src='bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'></script>
    <script src='xhprof/js/xhprof_report.js'></script>
    <script src='xhprof/js/list.js'></script>
    <script type="application/javascript">
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({html: true})
        });

//        $(function() {
//            var $tbl = $('#tbl'),
//                $compareButton = $('#b_cd');
//
//            function updateCompareButtonState() {
//                $cbs = $('#tbl input[type=checkbox]:checked');
//                if ($cbs.length == 2) {
//                    $compareButton.attr('disabled', false);
//                    $compareButton.attr('href', '?run1=' + $($cbs[0]).data('run-name') + '&run2=' + $($cbs[1]).data('run-name')
//                        + '&source=xhprof&source2=xhprof');
//                } else {
//                    $compareButton.attr('disabled', true);
//                    $compareButton.attr('href', '#');
//                }
//
//            }
//            $tbl.find('input[type=checkbox]').on('change', updateCompareButtonState);
//            updateCompareButtonState();
//        });
    </script>
</body>
</html>
