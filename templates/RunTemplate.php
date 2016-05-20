<?php

namespace Sugarcrm\XHProf\Viewer\Templates;

use \Sugarcrm\XHProf\Viewer\Templates\Common\Html\HeadTemplate as HtmlHead;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper;
use Sugarcrm\XHProf\Viewer\Templates\Run\OverallSummaryTemplate;

class RunTemplate
{
    public static function render($runData, $params, $xhprofData, $symbol)
    {
        global $totals;
        init_metrics($xhprofData, $symbol);

        // if we are reporting on a specific function, we can trim down
        // the report(s) to just stuff that is relevant to this function.
        // That way compute_flat_info()/compute_diff() etc. do not have
        // to needlessly work hard on churning irrelevant data.
        if (!empty($symbol)) {
            $xhprofData = xhprof_trim_run($xhprofData, array($symbol));
        }

        $symbol_tab = xhprof_compute_flat_info($xhprofData, $totals);

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
                'xhprof/js/queries.js',
                'xhprof/js/symbol-typeahead.js',
                'bower_components/lexer/lexer.js',
                'xhprof/js/sql-formatter.js',
            ));
        ?>
        <body class="container-fluid">
        <div>
            <div class="page-header form-inline" style="margin-top: 20px;">

                <div class="navbar-form pull-right" style="padding-right:0;">
                    <a class="btn btn-default btn-overall-summary">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                        <div><?php static::renderOverallSummary(); ?></div>
                    </a>
                    <a class="btn btn-primary" href="<?php echo $params['list_url'] ?>">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Back To List
                    </a>
                </div>
                <h1><p>SugarCRM XHProf Viewer </p><small><?php echo htmlentities($runData['namespace']) ?></small></h1>
            </div>
        </div>
        <?php profiler_report($params, $symbol, $xhprofData, $symbol_tab); ?>

        <script type="text/javascript">
            window.TYPEAHEAD_URL = '<?php echo static::typeAheadUrl() ?>';
            window.SYMBOL_URL = '<?php echo CurrentPageHelper::url() ?>';

            $(function () {
                $('[data-toggle="tooltip"]').tooltip({html: true})
            });
        </script>
        </body>
        </html>

        <?php
    }

    protected static function renderOverallSummary()
    {
        global $totals;
        global $metrics;
        global $sqlData;
        global $elasticData;
        global $unitSymbols;

        OverallSummaryTemplate::render(
            xhprof_get_possible_metrics(),
            $metrics,
            $totals,
            $sqlData,
            $elasticData,
            $unitSymbols
        );
    }

    protected static function typeAheadUrl()
    {
        return UrlHelper::url(array(
            'dir' => CurrentPageHelper::getParam('dir'),
            'run' => CurrentPageHelper::getParam('run'),
        ));
    }
}
