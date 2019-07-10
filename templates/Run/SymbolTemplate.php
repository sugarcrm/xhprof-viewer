<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Run;

use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\ShortenNameHelper;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper;
use Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate;

class SymbolTemplate
{
    public static function render($run_data, $symbol_info, $rep_symbol) {
        global $metrics;

        HeaderTemplate::prepareColumns($symbol_info, true);
        $columnsCount = HeaderTemplate::getColumnsCount();

        ?>
        <div class="panel panel-default panel-functions">
            <div class="panel-heading form-inline">
                <h3 class="panel-title" style="display: inline-block;">Parent/Child report for
                    <strong><?php ShortenNameHelper::render($rep_symbol, 45); ?></strong>
                </h3>
                <?php SymbolSearchInputTemplate::render() ?>
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo static::callgraphUrl($rep_symbol) ?>">
                    <i class="fa fa-pie-chart"></i> View Callgraph
                </a>
            </div>
            <table class="table table-functions table-condensed table-bordered">
                <?php HeaderTemplate::render() ?>
                <tr class="no-hover">
                    <td><b><i><center>Current Function</center></i></b></td>
                    <td colspan="<?php echo $columnsCount ?>"></td>
                </tr>
                <?php print_function_info($symbol_info) ?>
                <tr>
                    <?php $exclColumns = HeaderTemplate::getExclColumns();
                        foreach (HeaderTemplate::getColumns() as $column => $meta) { ?>
                        <?php if ($column == 'fn') { ?>
                            <td style='text-align:right;'>Exclusive Metrics for Current Function</td>
                        <?php } elseif (isset($exclColumns['excl_' . $column])) { ?>
                            <?php print_column_info($symbol_info, 'excl_' . $column,  $exclColumns['excl_' . $column]) ?>
                        <?php } else { ?>
                            <td></td>
                            <?php if (!empty($meta['percentage'])) { ?>
                                <td></td>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </tr>

                <?php // list of callers/parent functions
                $results = array();
//                $base_ct = $symbol_info["ct"];
                $base_info = array();
                foreach ($metrics as $metric) {
                    $base_info[$metric] = $symbol_info[$metric];
                }
                foreach ($run_data as $parent_child => $info) {
                    list($parent, $child) = xhprof_parse_parent_child($parent_child);
                    if (($child == $rep_symbol) && ($parent)) {
                        $info_tmp = $info;
                        $info_tmp["fn"] = $parent;
                        $results[] = $info_tmp;
                    }
                }
                usort($results, 'sort_cbk');

                if (count($results) > 0) {
                    $title = 'Parent functions';
                    if (count($results) > 1) {
                        $title .= 's';
                    }

                    print("<tr class=\"no-hover\"><td>");
                    print("<b><i><center>" . $title . "</center></i></b>");
                    print("</td><td colspan='$columnsCount'></td></tr>");

                    foreach ($results as $info) {
                        print_function_info($info);
                    }
                }

                // list of callees/child functions
                $results = array();
                $base_ct = 0;
                foreach ($run_data as $parent_child => $info) {
                    list($parent, $child) = xhprof_parse_parent_child($parent_child);
                    if ($parent == $rep_symbol) {
                        $info_tmp = $info;
                        $info_tmp["fn"] = $child;
                        $results[] = $info_tmp;
                        $base_ct += $info["ct"];
                    }
                }
                usort($results, 'sort_cbk');

                if (count($results)) {
                    $title = 'Child function';
                    if (count($results) > 1) {
                        $title .= 's';
                    }

                    print("<tr class=\"no-hover\"><td>");
                    print("<b><i><center>" . $title . "</center></i></b>");
                    print("</td><td colspan='$columnsCount'></td></tr>");

                    foreach ($results as $info) {
                        print_function_info($info);
                    }
                } ?>
            </table>
        </div>
        <?php
    }

    protected static function callgraphUrl($symbol)
    {
        return UrlHelper::url(array(
            'callgraph' => 1,
            'dir' => CurrentPageHelper::getParam('dir'),
            'run' => CurrentPageHelper::getParam('run'),
            'func' => $symbol,
        ));
    }
}
