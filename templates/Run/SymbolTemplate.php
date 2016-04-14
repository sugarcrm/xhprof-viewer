<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Run;

use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\ShortenNameHelper;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper;
use Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate;

class SymbolTemplate
{
    public static function render($url_params, $run_data, $symbol_info, $rep_symbol, $run1) {
        global $totals;
        global $pc_stats;
        global $sortable_columns;
        global $metrics;
        global $format_cbk;
        global $sort_col;

        $columnsCount = count($metrics) * 2 + 1 + 2;
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
                <?php HeaderTemplate::render($pc_stats, $sortable_columns) ?>
                <tr>
                    <td><b><i><center>Current Function</center></i></b></td>
                    <td colspan="<?php echo $columnsCount ?>"></td>
                </tr>
                <tr>
                    <td><a href=""><?php echo htmlspecialchars($rep_symbol) ?></a></td>
                    <td><?php echo $symbol_info['bcc'] ?></td>

                    <?php

                    print_td_num($symbol_info["ct"], $format_cbk["ct"]);
                    print_td_pct($symbol_info["ct"], $totals["ct"]);

                    // Inclusive Metrics for current function
                    foreach ($metrics as $metric) {
                        print_td_num($symbol_info[$metric], $format_cbk[$metric], ($sort_col == $metric));
                        print_td_pct($symbol_info[$metric], $totals[$metric], ($sort_col == $metric));
                    } ?>
                </tr>
                <tr>
                    <td style='text-align:right;'>Exclusive Metrics for Current Function</td>
                    <td></td>
                    <td></td>
                    <td></td>

                    // Exclusive Metrics for current function
                    foreach ($metrics as $metric) {
                        print_td_num(
                            $symbol_info["excl_" . $metric],
                            $format_cbk["excl_" . $metric],
                            ($sort_col == $metric),
                            get_tooltip_attributes("Child", $metric)
                        );

                        print_td_pct(
                            $symbol_info["excl_" . $metric],
                            $symbol_info[$metric],
                            ($sort_col == $metric),
                            get_tooltip_attributes("Child", $metric)
                        );
                    } ?>
                </tr>
                <?php // list of callers/parent functions
                $results = array();
                $base_ct = $symbol_info["ct"];
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
                    print_pc_array($url_params, $results, $base_ct, $base_info, true);
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
                    print_pc_array($url_params, $results, $base_ct, $base_info, false);
                } ?>
            </table>
        </div>
        <?php
    }

    protected static function callgraphUrl($symbol)
    {
        return UrlHelper::url(array(
            'dir' => CurrentPageHelper::getParam('dir'),
            'run' => CurrentPageHelper::getParam('run'),
            'func' => $symbol,
        ));
    }
}
