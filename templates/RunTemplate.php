<?php

namespace Sugarcrm\XHProf\Viewer\Templates;

use \Sugarcrm\XHProf\Viewer\Templates\Common\Html\HeadTemplate as HtmlHead;

class RunTemplate
{
    public static function render($runData, $params, $xhprofData, $run, $symbol, $sort)
    {
        ?><!DOCTYPE HTML><html>
        <?php HtmlHead::render(
            $runData['namespace'] . ' - SugarCRM XHProf Viewer',
            array(
                'xhprof/css/xhprof.css',
                'bower_components/bootstrap/dist/css/bootstrap.min.css',
                'bower_components/highlightjs/styles/default.css',
                'bower_components/font-awesome/css/font-awesome.min.css',
            ),
            array(
                'bower_components/jquery/dist/jquery.min.js',
                'bower_components/bootstrap/dist/js/bootstrap.min.js',
                'bower_components/highlightjs/highlight.pack.min.js',
                'bower_components/algolia-autocomplete.js/dist/autocomplete.jquery.min.js',
                'xhprof/js/queries.js',
                'xhprof/js/symbol-typeahead.js',
            ));
        ?>
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
        <?php profiler_single_run_report($params, $xhprofData, '', $symbol, $sort, $run); ?>

        <script type="text/javascript">
            window.TYPEAHEAD_URL = '<?php echo xhp_typeahead_url() ?>';
            window.SYMBOL_URL = '<?php echo xhp_run_url() ?>';

            $(function () {
                $('[data-toggle="tooltip"]').tooltip({html: true})
            });
        </script>
        </body>
        </html>

        <?php
    }
}
