<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates;

use Sugarcrm\XHProf\Viewer\Templates\Common\Html\HeadTemplate as HtmlHead;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use Sugarcrm\XHProf\Viewer\Templates\Run\SqlQueriesTableTemplate;use Sugarcrm\XHProf\Viewer\Templates\Run\TopTabsTemplate;

class SqlPageTemplate
{
    public static function render($runData, $sqlData)
    {
        ?><!DOCTYPE HTML><html>
        <?php HtmlHead::render(
        'SQL Queries - ' . $runData['namespace'] . ' - SugarCRM XHProf Viewer',
        array(
            'xhprof/css/xhprof.css',
            'node_modules/bootstrap/dist/css/bootstrap.min.css',
            'node_modules/highlightjs/styles/default.css',
            'node_modules/font-awesome/css/font-awesome.min.css',
            'xhprof/css/run-page.css',
        ),
        array(
            'node_modules/jquery/dist/jquery.min.js',
            'node_modules/bootstrap/dist/js/bootstrap.min.js',
            'node_modules/highlightjs/highlight.pack.min.js',
            'xhprof/js/queries.js',
            'xhprof/js/lexer/lexer.js',
            'xhprof/js/sql-formatter.js',
        ));
        ?>
        <body class="container-fluid">
        <div>
            <div class="page-header form-inline" style="margin-top: 20px;">

                <div class="navbar-form pull-right" style="padding-right:0;">
                    <a class="btn btn-default btn-overall-summary">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
<!--                        <div>--><?php //static::renderOverallSummary(); ?><!--</div>-->
                    </a>
                    <a class="btn btn-primary" href="<?php echo htmlspecialchars(CurrentPageHelper::getParam('list_url')) ?>">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Back To List
                    </a>
                </div>
                <h1><p>SugarCRM XHProf Viewer </p><small><?php echo htmlspecialchars($runData['namespace']) ?></small></h1>
            </div>
        </div>

        <?php TopTabsTemplate::render($runData) ?>
        <?php SqlQueriesTableTemplate::render('SQL Queries', $sqlData, 'sql') ?>

        <script type="text/javascript">
            $(function () {
                $('[data-toggle="tooltip"]').tooltip({html: true})
            });
        </script>
        </body>
        </html><?php
    }
}
