<html>
    <head>
        <title>XHProf: Hierarchical Profiler Report</title>
        <link rel="shortcut icon" type="image/png" href="xhprof/images/guitarist-309806_640.png"/>

        <link href='xhprof/css/xhprof.css' rel='stylesheet' type='text/css' />
        <link href='bower_components/bootstrap/dist/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
        <link href='bower_components/highlightjs/styles/default.css' rel='stylesheet' type='text/css' />
        <link href='bower_components/font-awesome/css/font-awesome.min.css' rel='stylesheet' type='text/css' />

        <script src='bower_components/jquery/dist/jquery.min.js'></script>
        <script src='bower_components/highlightjs/highlight.pack.min.js'></script>
        <script src='bower_components/algolia-autocomplete.js/dist/autocomplete.jquery.min.js'></script>
        <script src='bower_components/bootstrap/dist/js/bootstrap.min.js'></script>
        <script src='xhprof/js/xhprof_report.js'></script>
    </head>
    <body class="container-fluid">
        <div>
            <div class="page-header form-inline" style="margin-top: 20px;">

                <div class="navbar-form pull-right" style="padding-right:0;">
                    <a class="btn btn-primary" href="<?php echo $params['list_url'] ?>">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Back To List
                    </a>
                </div>
                <h1><p>SugarCRM XHProf Viewer </p><small><?php echo htmlentities($runData['namespace']) ?></small></h1>
            </div>
        </div>

        <?php displayXHProfReport($xhprof_runs_impl, $params, $source, $run, $wts,
            $symbol, $sort, $run1, $run2, $source2); ?>

        <script src='xhprof/js/queries.js'></script>
        <script src='xhprof/js/symbol-typeahead.js'></script>
        <script type="text/javascript">
            window.TYPEAHEAD_URL = '<?php echo xhp_typeahead_url() ?>';
            window.SYMBOL_URL = '<?php echo xhp_run_url() ?>';

            $(function () {
                $('[data-toggle="tooltip"]').tooltip({html: true})
            });
        </script>
    </body>
</html>
