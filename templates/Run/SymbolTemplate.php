<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Run;

use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use \Sugarcrm\XHProf\Viewer\Templates\Helpers\ShortenNameHelper;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper;

class SymbolTemplate
{
    public static function render($url_params, $run_data, $symbol_info, $rep_symbol, $run1) {
        global $totals;
        global $pc_stats;
        global $sortable_columns;
        global $metrics;
        global $format_cbk;
        global $sort_col;
        global $display_calls;

        $columnsCount = count($metrics) * 2 + 1 + ($display_calls ? 2 : 0);
        ?>
        <div class="panel panel-default panel-functions">
            <div class="panel-heading form-inline">
                <h3 class="panel-title" style="display: inline-block;">Parent/Child report for
                    <strong><?php ShortenNameHelper::render($rep_symbol, 45); ?></strong>
                </h3>
                <?php display_symbol_search_input() ?>
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo static::callgraphUrl($rep_symbol) ?>">
                    <i class="fa fa-pie-chart"></i> View Callgraph
                </a>
            </div>
            <table class="table table-functions table-condensed table-bordered table-striped">
                <tr  align=right>
                <?php foreach ($pc_stats as $stat) { ?>
                    <th <?php if ($stat == "fn") { ?> align=left <?php } else { ?> class="vwbar" <?php } ?>>
                        <nobr>
                        <?php if (array_key_exists($stat, $sortable_columns)) { ?>
                            <a href="<?php echo CurrentPageHelper::url(array('sort' => $stat)); ?>">
                                <?php echo stat_description($stat); ?>
                            </a>
                        <?php } else { ?>
                            <?php echo stat_description($stat); ?>
                        <?php } ?>
                    </th>
                <?php } ?>
                </tr>
                <tr>
                    <td><b><i><center>Current Function</center></i></b></td>
                    <td colspan="<?php echo $columnsCount ?>"></td>
                </tr>
                <tr>
                    <td><a href=""><?php echo htmlspecialchars($rep_symbol) ?></a></td>
                    <td align="right"><?php echo $symbol_info['bcc'] ?></td>

                    <?php if ($display_calls) {
                        // Call Count
                        print_td_num($symbol_info["ct"], $format_cbk["ct"]);
                        print_td_pct($symbol_info["ct"], $totals["ct"]);
                    }

                    // Inclusive Metrics for current function
                    foreach ($metrics as $metric) {
                        print_td_num($symbol_info[$metric], $format_cbk[$metric], ($sort_col == $metric));
                        print_td_pct($symbol_info[$metric], $totals[$metric], ($sort_col == $metric));
                    } ?>
                </tr>
                <tr bgcolor='#ffffff'>
                    <td style='text-align:right;'>Exclusive Metrics for Current Function</td>
                    <td></td>

                    <?php if ($display_calls) { ?>
                        <td class="vbar"></td>
                        <td class="vbar"></td>
                    <?php }

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
                if ($display_calls) {
                    $base_ct = $symbol_info["ct"];
                } else {
                    $base_ct = 0;
                }
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
                    print_pc_array($url_params, $results, $base_ct, $base_info, true,
                        $run1, 0);
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
                        if ($display_calls) {
                            $base_ct += $info["ct"];
                        }
                    }
                }
                usort($results, 'sort_cbk');

                if (count($results)) {
                    print_pc_array($url_params, $results, $base_ct, $base_info, false, $run1, 0);
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
