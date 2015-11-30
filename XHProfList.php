<html>
<head>
    <title>XHProf: Hierarchical Profiler Report</title>
    <link href='xhprof/css/xhprof.css' rel='stylesheet' type='text/css' />
    <link href='xhprof/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
    <link href='xhprof/css/bootstrap-datepicker.min.css' rel='stylesheet' type='text/css' />
    <script src='xhprof/jquery/jquery-2.1.4.min.js'></script>
</head>
<body class="container-fluid">
<div>
    <div class="top-right-link"><a href="?">Refresh</a></div>


    <h4 class="align-center">List of profiler files</h4>
    <div style="clear:both;">&nbsp;</div>
</div>

<div class="row">

    <div class="col-md-8">
        <nav class="navbar navbar-default" role="navigation">
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <form action="" method="get">
                    <input type="hidden" name="offset" value="0" />
                    <input type="hidden" name="f_sort_by" value="<?=$filters['f_sort_by']?>" />
                    <input type="hidden" name="f_sort_dir" value="<?=$filters['f_sort_dir']?>" />
                    <ul class="nav navbar-nav">
                        <li><a href="#">Date from</a></li>
                        <li class=" navbar-form"><input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                                                        placeholder="Date From" name="f_date_from" id="date_1" value="<?=$filters['f_date_from']?>"></li>
                        <li><a href="#">to</a></li>
                        <li class=" navbar-form"><input style="width:104px" type="text" class="form-control datepicker-dropdown" data-provide="datepicker"
                                                        placeholder="Date To" name="f_date_to" id="date_2" value="<?=$filters['f_date_to']?>"></li>
                        <li>
                            <a href="#">Results on page</a>
                        </li>
                        <li class=" navbar-form">
                            <select class="form-control" name="limit">
                                <? foreach ([10, 50, 100, 500, 1000, 10000, 100000] as $val) { ?>
                                    <option value="<?=$val?>"<?=$val == $limit ? ' selected' : ''?>><?=$val?></option>
                                <? } ?>
                            </select>
                        </li>
                        <li class=" navbar-form">
                            <button class="btn btn-default" type="submit"><b>APLLY</b></button>
                        </li>
                    </ul>
                    <div class="col-sm-3 col-md-3 pull-right navbar-form">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search" name="f_text" value="<?=$filters['f_text']?>">
                            <div class="input-group-btn">
                                <button class="btn btn-default" type="submit"><b>APLLY</b></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </nav>
    </div>

    <div class="col-md-4">
        <span style="float:right; margin: 0 40px">
    <?

    $filterValues = [
        "f_date_from={$filters['f_date_from']}",
        "f_date_to={$filters['f_date_to']}",
        "f_text={$filters['f_text']}",
    ];
    $url = "?" . implode('&', $filterValues) . "&limit=$limit";

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
        Results total: <?=$total?> / <?=$pages?> pages

        <ul class="pagination pagination-sm">
            <? $liClass = ($startPage > 1) ? '' : ' class="disabled"' ?>
            <li<?=$liClass?>><a href="<?=$url?>&offset=0">&lt;&lt;&lt; </a></li>
            <li<?=$liClass?>><a href="<?=$url?>&offset=<?=$offset-1?>">&lt; </a></li>

            <? for($i = 1; $i <= $pagesBefore; $i ++) { ?>
                <li><a href="<?=$url?>&offset=<?=$startPage - ($pagesBefore - $i + 1) - 1?>"><?=$startPage - ($pagesBefore - $i + 1)?></a></li>
            <? } ?>

            <li class="active"><a href="<?=$url?>&offset=<?=$startPage - 1?>"><?=$startPage?></a></li>

            <? for($i = 1; $i <= $pagesAfter; $i ++) { ?>
                <li><a href="<?=$url?>&offset=<?=$i + $startPage - 1?>"><?=$i + $startPage?></a></li>
            <? } ?>

            <? $liClass = ($pagesAfter > 0) ? '' : ' class="disabled"' ?>
            <li<?=$liClass?>><a href="<?=$url?>&offset=<?=$offset+1?>"> &gt;</a></li>
            <li<?=$liClass?>><a href="<?=$url?>&offset=<?=$pages-1?>"> &gt;&gt;&gt;</a></li>
        </ul>
        <?
    } else { ?>
        Results total: <?=$total?>
    <? } ?>
    </span>
    </div>
</div>

<div style="clear:both">
    <button id="b_cd" class="btn btn-default" disabled="disabled" onclick="checkdiff()" >Check difference between 2 files</button>
</div>

<?

$sortMarker = $filters['f_sort_dir'] == 'desc' ? '<span class="dropup"><span class="caret"></span></span>' : '<span class="caret"></span>';
$url .= "&offset=0";

?>

<table id="tbl" style="border-top:1px solid #ccc" class="table table-condensed table-striped table-bordered">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>#</th>
        <th>Profile</th>
        <th class="align-right sortable"
            ><a href="<?=$url . '&f_sort_by=wt' . ($filters['f_sort_by'] == 'wt' ? ($filters['f_sort_dir'] == 'desc' ? '' : '&f_sort_dir=desc') : '')?>"
                >Wall Time<?=$filters['f_sort_by'] == 'wt' ? $sortMarker : '' ?></a>
        </th>
        <th class="align-right sortable">
            <a href="<?=$url . '&f_sort_by=sql' . ($filters['f_sort_by'] == 'sql' ? ($filters['f_sort_dir'] == 'desc' ? '' : '&f_sort_dir=desc') : '')?>"
                >SQL<?=$filters['f_sort_by'] == 'sql' ? $sortMarker : '' ?></a>
        </th>
        <th class="align-right sortable">
            <a href="<?=$url . '&f_sort_by=ts' . ($filters['f_sort_by'] == 'ts' ? ($filters['f_sort_dir'] == 'desc' ? '' : '&f_sort_dir=desc') : '')?>"
                >Timestamp<?=$filters['f_sort_by'] == 'ts' ? $sortMarker : '' ?></a>
        </th>
        <th class="align-right sortable">
            <a href="<?=$url . '&f_sort_by=fs' . ($filters['f_sort_by'] == 'fs' ? ($filters['f_sort_dir'] == 'desc' ? '' : '&f_sort_dir=desc') : '')?>"
                >File Size<?=$filters['f_sort_by'] == 'fs' ? $sortMarker : '' ?></a>
        </th>
    </tr>
    </thead>
    <tbody>

    <?
    $lastTestTime = 0;
    $microtime = microtime(1);
    foreach ($files as $index => $file) {
        if (!$lastTestTime || abs($file['timestamp'] - $lastTestTime) > 5) { ?>
            <tr>
                <td colspan="7" class="align-center age-bar">
                    <?= $this->toTimePcs($microtime-$file['stat']['mtime']); ?> ago
                </td>
            </tr>
        <? }
        $lastTestTime = $file['timestamp'];
        ?>

        <tr>
            <td><input type="checkbox" onclick="setTimeout('checkbutton()',20)" name="<?=urlencode($file['file'])?>"></td>
            <td><?=$index+1?></td>
            <td><a title="<?=strlen($file['namespace']) > 100 ? $file['namespace'] : '' ?>"
                   href="?run=<?=$file['pi']['filename']?>&source=<?=$file['pi']['extension']?>">
                    <? $name = substr($file['namespace'], 0, 100) . '' . (strlen($file['namespace']) > 100 ? ' ...' : '') ?>
                    <?=preg_replace("/" . preg_quote($searchText) . "/i", "<b style='color:black;background-color:yellow;'>$0</b>", $name)?>
                </a>
            </td>
            <td class="align-right"><?=number_format($file['wall_time'], 0, ' ', ' ')?></td>
            <td class="align-right">
                <?if ($this->hasSqlFile($file['path'])) { ?>
                    <?=$file['sql_queries']?>
                <? } else { ?>
                    <?=$file['sql_queries']?> (no file)
                <? } ?>
            </td>
            <td class="align-right"><?=date('Y-m-d H:i:s', $file['timestamp'])?></td>
            <td class="align-right"><?=$this->toBytes($file['stat']['size'])?></td>
        </tr>
    <? } ?>
    </tbody>
</table>

<script src='xhprof/js/bootstrap-datepicker.min.js'></script>
</body>
</html>
