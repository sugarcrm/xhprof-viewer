<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

/*
 * Formats call counts for XHProf reports.
 *
 * Description:
 * Call counts in single-run reports are integer values.
 * However, call counts for aggregated reports can be
 * fractional. This function will print integer values
 * without decimal point, but with commas etc.
 *
 *   4000 ==> 4,000
 *
 * It'll round fractional values to decimal precision of 3
 *   4000.1212 ==> 4,000.121
 *   4000.0001 ==> 4,000
 *
 */
use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use Sugarcrm\XHProf\Viewer\Templates\Run\SymbolSearchInputTemplate;

function xhprof_count_format($num) {
    $num = round($num, 3);
    if (round($num) == $num) {
        return number_format($num);
    } else {
        return number_format($num, 3);
    }
}

function xhprof_percent_format($s, $precision = 1) {
    return sprintf('%.'.$precision.'f%%', 100*$s);
}

// default column to sort on -- wall time
$sort_col = "wt";

$unitSymbols = array(
    'byte' => 'B',
    'microsec' => 'μs'
);

$possible_metrics = array(
    "wt" => array("Wall", $unitSymbols['microsec'], "walltime"),
    "ut" => array("User", $unitSymbols['microsec'], "user cpu time"),
    "st" => array("Sys", $unitSymbols['microsec'], "system cpu time"),
    "cpu" => array("Cpu", $unitSymbols['microsec'], "cpu time"),
    "mu" => array("MUse", $unitSymbols['byte'], "memory usage"),
    "pmu" => array("PMUse", $unitSymbols['byte'], "peak memory usage"),
    "samples" => array("Samples", "samples", "cpu time")
);

// Textual descriptions for column headers in "single run" mode
$descriptions = array(
    "fn" => "Function Name",
    "bcc" => "SQL",
    "ct" =>  "Calls",

    "wt" => "Incl. Wall Time (" . $unitSymbols['microsec'] . ")",
    "excl_wt" => "Excl. Wall Time (" . $unitSymbols['microsec'] . ")",

    "ut" => "Incl. User (" . $unitSymbols['microsec'] . ")",
    "excl_ut" => "Excl. User (" . $unitSymbols['microsec'] . ")",

    "st" => "Incl. Sys (" . $unitSymbols['microsec'] . ")",
    "excl_st" => "Excl. Sys (" . $unitSymbols['microsec'] . ")",

    "cpu" => "Incl. CPU (" . $unitSymbols['microsec'] . ")",
    "excl_cpu" => "Excl. CPU (" . $unitSymbols['microsec'] . ")",

    "mu" => "Incl. MemUse (" . $unitSymbols['byte'] . ")",
    "excl_mu" => "Excl. MemUse (" . $unitSymbols['byte'] . ")",

    "pmu" => "Incl. PeakMemUse (" . $unitSymbols['byte'] . ")",
    "excl_pmu" => "Excl. PeakMemUse (" . $unitSymbols['byte'] . ")",

    "samples" => "Incl. Samples",
    "excl_samples" => "Excl. Samples",
);

// Various total counts
$totals = array();

/*
 * The subset of $possible_metrics that is present in the raw profile data.
 */
$metrics = null;

/**
 * Callback comparison operator (passed to usort() for sorting array of
 * tuples) that compares array elements based on the sort column
 * specified in $sort_col (global parameter).
 *
 * @author Kannan
 */
function sort_cbk($a, $b)
{
    global $sort_col;

    if ($sort_col == "fn") {

        // case insensitive ascending sort for function names
        $left = strtoupper($a["fn"]);
        $right = strtoupper($b["fn"]);

        if ($left == $right)
            return 0;
        return ($left < $right) ? -1 : 1;

    } else {

        // descending sort for all others
        $left = $a[$sort_col];
        $right = $b[$sort_col];

        if ($left == $right)
            return 0;
        return ($left > $right) ? -1 : 1;
    }
}

/**
 * Initialize the metrics we'll display based on the information
 * in the raw data.
 *
 * @author Kannan
 */
function init_metrics($xhprof_data, $rep_symbol) {
    global $metrics;
    global $sort_col;

    // For C++ profiler runs, walltime attribute isn't present.
    // In that case, use "samples" as the default sort column.
    if (!isset($xhprof_data["main()"]["wt"])) {
        if ($sort_col == "wt") {
            $sort_col = "samples";
        }
    }

    // parent/child report doesn't support exclusive times yet.
    // So, change sort hyperlinks to closest fit.
    if (!empty($rep_symbol)) {
        $sort_col = str_replace("excl_", "", $sort_col);
    }

    $possible_metrics = xhprof_get_possible_metrics();
    foreach ($possible_metrics as $metric => $desc) {
        if (isset($xhprof_data["main()"][$metric])) {
            $metrics[] = $metric;
        }
    }
}

/**
 * Get the appropriate description for a statistic
 * (depending upon whether we are in diff report mode
 * or single run report mode).
 *
 * @author Kannan
 */
function stat_description($stat) {
    global $descriptions;
    return $descriptions[$stat];
}


/**
 * Analyze raw data & generate the profiler report
 * (common for both single run mode and diff mode).
 *
 * @author: Kannan
 */
function profiler_report ($params, $symbol, $xhprofData, $symbol_tab) {
    // data tables
    if (!empty($symbol)) {
        if (!isset($symbol_tab[$symbol])) {
            echo "<hr>Symbol <b>$symbol</b> not found in XHProf run</b><hr>";
            return;
        }

        $data = $symbol_tab[$symbol];
        $data['fn'] = $symbol;

        \Sugarcrm\XHProf\Viewer\Templates\Run\SymbolTemplate::render(
            $xhprofData,
            $data,
            $symbol
        );
    } else {
        /* flat top-level report of all functions */
        full_report($params, $symbol_tab);
    }
}

/**
 * Print "flat" data corresponding to one function.
 *
 * @author Kannan
 */
function print_function_info($info) {
    print('<tr>');
    foreach (\Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate::getColumns() as $column => $meta) {
        print_column_info($info, $column, $meta);
    }
    print("</tr>\n");
}

function print_column_info($info, $column, $meta) {
    global $totals;

    $class = CurrentPageHelper::getParam('sort') == $column ? 'class="sorted-by"' : '';

    print("<td $class>");
    $cb = isset($meta['cb']) ? $meta['cb'] : 'number_format';
    echo call_user_func($cb, $info[$column]);
    print('</td>');

    if (!empty($meta['percentage'])) {
        print("<td $class>");

        $totalColumn = !empty($meta['total']) ? $meta['total'] : $column;
        if ($totals[$totalColumn] != 0) {
            echo xhprof_percent_format($info[$column] / abs($totals[$totalColumn]));
        } else {
            echo 'N/A%';
        }

        print('</td>');
    }
}

/**
 * Print non-hierarchical (flat-view) of profiler data.
 *
 * @author Kannan
 */
function print_flat_data($title, $flat_data, $limit, $callGraphButton) {

    $size  = count($flat_data);
    if (!$limit) {
        $limit = $size;
        $display_link = "";
    } else {
        $display_link = "<a href='" . CurrentPageHelper::url(array('all' => 1))
            . "' class='btn btn-sm btn-primary'>Display All</a>";
    }

    print('<div class="panel panel-default panel-functions">');

    print("<div class=\"panel-heading form-inline \"><h3 class=\"panel-title\" style='display:inline-block;'>$title</h3> ");
    SymbolSearchInputTemplate::render();
    echo "$display_link $callGraphButton";
    print("</div>");
    print('<table class="table table-functions table-condensed table-bordered">');
    \Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate::prepareColumns(reset($flat_data));
    \Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate::render();

    if ($limit >= 0) {
        $limit = min($size, $limit);
        for($i=0; $i < $limit; $i++) {
            print_function_info($flat_data[$i]);
        }
    } else {
        // if $limit is negative, print abs($limit) items starting from the end
        $limit = min($size, abs($limit));
        for($i=0; $i < $limit; $i++) {
            print_function_info($flat_data[$size - $i - 1]);
        }
    }
    print("</table>");

    // let's print the display all link at the bottom as well...
    if ($display_link) {
        echo '<div class="panel-footer">' . $display_link . '</div>';
    }

    print('</div>');

}

/**
 * Generates a tabular report for all functions. This is the top-level report.
 *
 * @author Kannan
 */
function full_report($url_params, $symbol_tab) {
    global $sqlData;
    global $elasticData;

    $callgraph_report_title = '<i class="fa fa-pie-chart"></i> View Full Callgraph';

    \Sugarcrm\XHProf\Viewer\Templates\Run\SqlQueriesTableTemplate::render('SQL Queries', $sqlData, 'sql');
    \Sugarcrm\XHProf\Viewer\Templates\Run\QueriesTableTemplate::render('Elastic Queries', $elasticData, 'bash');

    $callGraphUrl = \Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper::url(array(
        'callgraph' => 1,
        'dir' => CurrentPageHelper::getParam('dir'),
        'run' => CurrentPageHelper::getParam('run'),
    ));

    $callGraphButton = '<a class="btn btn-primary btn-sm" target="_blank" href="' . $callGraphUrl . '">'
        . $callgraph_report_title . '</a>';

    $flat_data = array();
    foreach ($symbol_tab as $symbol => $info) {
        $tmp = $info;
        $tmp["fn"] = $symbol;
        if(!isset($tmp['bcc'])){
            $tmp['bcc']='';
        }
        $flat_data[] = $tmp;
    }
    usort($flat_data, 'sort_cbk');

    if (!empty($url_params['all'])) {
        $limit = 0;    // display all rows
    } else {
        $limit = 100;  // display only limited number of rows
    }

    print_flat_data('Top-Level Report &nbsp;', $flat_data, $limit, $callGraphButton);
}
