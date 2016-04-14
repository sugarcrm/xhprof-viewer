<?php

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

/**
 * @param html-str $content  the text/image/innerhtml/whatever for the link
 * @param raw-str  $href
 * @param raw-str  $class
 * @param raw-str  $id
 * @param raw-str  $title
 * @param raw-str  $target
 * @param raw-str  $onclick
 * @param raw-str  $style
 * @param raw-str  $access
 * @param raw-str  $onmouseover
 * @param raw-str  $onmouseout
 * @param raw-str  $onmousedown
 * @param raw-str  $dir
 * @param raw-str  $rel
 */
function xhprof_render_link($content, $href, $class='', $id='', $title='',
                            $target='',
                            $onclick='', $style='', $access='', $onmouseover='',
                            $onmouseout='', $onmousedown='') {

    if (!$content) {
        return '';
    }

    if ($href) {
        $link = '<a href="' . ($href) . '"';
    } else {
        $link = '<span';
    }

    if ($class) {
        $link .= ' class="' . ($class) . '"';
    }
    if ($id) {
        $link .= ' id="' . ($id) . '"';
    }
    if ($title) {
        $link .= ' title="' . ($title) . '"';
    }
    if ($target) {
        $link .= ' target="' . ($target) . '"';
    }
    if ($onclick && $href) {
        $link .= ' onclick="' . ($onclick) . '"';
    }
    if ($style && $href) {
        $link .= ' style="' . ($style) . '"';
    }
    if ($access && $href) {
        $link .= ' accesskey="' . ($access) . '"';
    }
    if ($onmouseover) {
        $link .= ' onmouseover="' . ($onmouseover) . '"';
    }
    if ($onmouseout) {
        $link .= ' onmouseout="' . ($onmouseout) . '"';
    }
    if ($onmousedown) {
        $link .= ' onmousedown="' . ($onmousedown) . '"';
    }

    $link .= '>';
    $link .= $content;
    if ($href) {
        $link .= '</a>';
    } else {
        $link .= '</span>';
    }

    return $link;
}


// default column to sort on -- wall time
$sort_col = "wt";

// The following column headers are sortable
$sortable_columns = array("fn" => 1,
    "bcc" => 1,
    "ct" => 1,
    "wt" => 1,
    "excl_wt" => 1,
    "ut" => 1,
    "excl_ut" => 1,
    "st" => 1,
    "excl_st" => 1,
    "mu" => 1,
    "excl_mu" => 1,
    "pmu" => 1,
    "excl_pmu" => 1,
    "cpu" => 1,
    "excl_cpu" => 1,
    "samples" => 1,
    "excl_samples" => 1
);

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
    "bcc" => "Caused<br>SQL",
    "ct" =>  "Calls",
    "Calls%" => "Calls%",

    "wt" => "Incl. Wall Time<br>(" . $unitSymbols['microsec'] . ")",
    "IWall%" => "IWall%",
    "excl_wt" => "Excl. Wall Time<br>(" . $unitSymbols['microsec'] . ")",
    "EWall%" => "EWall%",

    "ut" => "Incl. User<br>(" . $unitSymbols['microsec'] . ")",
    "IUser%" => "IUser%",
    "excl_ut" => "Excl. User<br>(" . $unitSymbols['microsec'] . ")",
    "EUser%" => "EUser%",

    "st" => "Incl. Sys <br>(" . $unitSymbols['microsec'] . ")",
    "ISys%" => "ISys%",
    "excl_st" => "Excl. Sys <br>(" . $unitSymbols['microsec'] . ")",
    "ESys%" => "ESys%",

    "cpu" => "Incl. CPU<br>(" . $unitSymbols['microsec'] . ")",
    "ICpu%" => "ICpu%",
    "excl_cpu" => "Excl. CPU<br>(" . $unitSymbols['microsec'] . ")",
    "ECpu%" => "ECPU%",

    "mu" => "Incl.<br>MemUse<br>(" . $unitSymbols['byte'] . ")",
    "IMUse%" => "IMemUse%",
    "excl_mu" => "Excl.<br>MemUse<br>(" . $unitSymbols['byte'] . ")",
    "EMUse%" => "EMemUse%",

    "pmu" => "Incl.<br> PeakMemUse<br>(" . $unitSymbols['byte'] . ")",
    "IPMUse%" => "IPeakMemUse%",
    "excl_pmu" => "Excl.<br>PeakMemUse<br>(" . $unitSymbols['byte'] . ")",
    "EPMUse%" => "EPeakMemUse%",

    "samples" => "Incl. Samples",
    "ISamples%" => "ISamples%",
    "excl_samples" => "Excl. Samples",
    "ESamples%" => "ESamples%",
);

// Formatting Callback Functions...
$format_cbk = array(
    "fn" => "",
    "bcc" => "xhprof_count_format",
    "ct" => "xhprof_count_format",
    "Calls%" => "xhprof_percent_format",

    "wt" => "number_format",
    "IWall%" => "xhprof_percent_format",
    "excl_wt" => "number_format",
    "EWall%" => "xhprof_percent_format",

    "ut" => "number_format",
    "IUser%" => "xhprof_percent_format",
    "excl_ut" => "number_format",
    "EUser%" => "xhprof_percent_format",

    "st" => "number_format",
    "ISys%" => "xhprof_percent_format",
    "excl_st" => "number_format",
    "ESys%" => "xhprof_percent_format",

    "cpu" => "number_format",
    "ICpu%" => "xhprof_percent_format",
    "excl_cpu" => "number_format",
    "ECpu%" => "xhprof_percent_format",

    "mu" => "number_format",
    "IMUse%" => "xhprof_percent_format",
    "excl_mu" => "number_format",
    "EMUse%" => "xhprof_percent_format",

    "pmu" => "number_format",
    "IPMUse%" => "xhprof_percent_format",
    "excl_pmu" => "number_format",
    "EPMUse%" => "xhprof_percent_format",

    "samples" => "number_format",
    "ISamples%" => "xhprof_percent_format",
    "excl_samples" => "number_format",
    "ESamples%" => "xhprof_percent_format",
);


// Textual descriptions for column headers in "diff" mode
$diff_descriptions = array(
    "fn" => "Function Name",
    "bcc" =>  "Caused<br>SQL",
    "ct" =>  "Calls Diff",
    "Calls%" => "Calls<br>Diff%",

    "wt" => "Incl. Wall<br>Diff<br>(microsec)",
    "IWall%" => "IWall<br> Diff%",
    "excl_wt" => "Excl. Wall<br>Diff<br>(microsec)",
    "EWall%" => "EWall<br>Diff%",

    "ut" => "Incl. User Diff<br>(microsec)",
    "IUser%" => "IUser<br>Diff%",
    "excl_ut" => "Excl. User<br>Diff<br>(microsec)",
    "EUser%" => "EUser<br>Diff%",

    "cpu" => "Incl. CPU Diff<br>(microsec)",
    "ICpu%" => "ICpu<br>Diff%",
    "excl_cpu" => "Excl. CPU<br>Diff<br>(microsec)",
    "ECpu%" => "ECpu<br>Diff%",

    "st" => "Incl. Sys Diff<br>(microsec)",
    "ISys%" => "ISys<br>Diff%",
    "excl_st" => "Excl. Sys Diff<br>(microsec)",
    "ESys%" => "ESys<br>Diff%",

    "mu" => "Incl.<br>MemUse<br>Diff<br>(bytes)",
    "IMUse%" => "IMemUse<br>Diff%",
    "excl_mu" => "Excl.<br>MemUse<br>Diff<br>(bytes)",
    "EMUse%" => "EMemUse<br>Diff%",

    "pmu" => "Incl.<br> PeakMemUse<br>Diff<br>(bytes)",
    "IPMUse%" => "IPeakMemUse<br>Diff%",
    "excl_pmu" => "Excl.<br>PeakMemUse<br>Diff<br>(bytes)",
    "EPMUse%" => "EPeakMemUse<br>Diff%",

    "samples" => "Incl. Samples Diff",
    "ISamples%" => "ISamples Diff%",
    "excl_samples" => "Excl. Samples Diff",
    "ESamples%" => "ESamples Diff%",
);

// columns that'll be displayed in a top-level report
$stats = array();

// columns that'll be displayed in a function's parent/child report
$pc_stats = array();

// Various total counts
$totals = 0;
$totals_1 = 0;
$totals_2 = 0;

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
function init_metrics($xhprof_data, $rep_symbol, $sort) {
    global $stats;
    global $pc_stats;
    global $metrics;
    global $sortable_columns;
    global $sort_col;

    if (!empty($sort)) {
        if (array_key_exists($sort, $sortable_columns)) {
            $sort_col = $sort;
        } else {
            print("Invalid Sort Key $sort specified in URL");
        }
    }

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

    $stats = array("fn", "bcc", "ct", "Calls%");
    $pc_stats = $stats;

    $possible_metrics = xhprof_get_possible_metrics();
    foreach ($possible_metrics as $metric => $desc) {
        if (isset($xhprof_data["main()"][$metric])) {
            $metrics[] = $metric;
            // flat (top-level reports): we can compute
            // exclusive metrics reports as well.
            $stats[] = $metric;
            $stats[] = "I" . $desc[0] . "%";
            $stats[] = "excl_" . $metric;
            $stats[] = "E" . $desc[0] . "%";

            // parent/child report for a function: we can
            // only breakdown inclusive times correctly.
            $pc_stats[] = $metric;
            $pc_stats[] = "I" . $desc[0] . "%";
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
function profiler_report ($url_params, $rep_symbol, $run1, $run1_data) {
    global $totals;

    // if we are reporting on a specific function, we can trim down
    // the report(s) to just stuff that is relevant to this function.
    // That way compute_flat_info()/compute_diff() etc. do not have
    // to needlessly work hard on churning irrelevant data.
    if (!empty($rep_symbol)) {
        $run1_data = xhprof_trim_run($run1_data, array($rep_symbol));
    }

    $symbol_tab = xhprof_compute_flat_info($run1_data, $totals);
    $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params, 'symbol'), 'all');
    $top_link_query_string = "?" . http_build_query($base_url_params);

    $diff_text = "Run";

    // set up the action links for operations that can be done on this report
    $links = array();
    $links []=  xhprof_render_link("View Top Level $diff_text Report",
        $top_link_query_string);

    // lookup function typeahead form
    $links [] = '<input class="function_typeahead" ' .
        ' type="input" size="40" maxlength="100" />';

    // data tables
    if (!empty($rep_symbol)) {
        if (!isset($symbol_tab[$rep_symbol])) {
            echo "<hr>Symbol <b>$rep_symbol</b> not found in XHProf run</b><hr>";
            return;
        }

        \Sugarcrm\XHProf\Viewer\Templates\Run\SymbolTemplate::render(
            $url_params,
            $run1_data,
            $symbol_tab[$rep_symbol],
            $rep_symbol,
            $run1);
    } else {
        /* flat top-level report of all functions */
        full_report($url_params, $symbol_tab);
    }
}

/**
 * Computes percentage for a pair of values, and returns it
 * in string format.
 */
function pct($a, $b) {
    if ($b == 0) {
        return "N/A";
    } else {
        $res = (round(($a * 1000 / $b)) / 10);
        return $res;
    }
}

/**
 * Prints a <td> element with a numeric value.
 */
function print_td_num($num, $fmt_func, $bold=false, $attributes=null) {
    if (!empty($fmt_func)) {
        $num = call_user_func($fmt_func, $num);
    }

    print("<td $attributes>$num</td>\n");
}

/**
 * Prints a <td> element with a pecentage.
 */
function print_td_pct($numer, $denom, $bold=false, $attributes=null) {
    if ($denom == 0) {
        $pct = "N/A%";
    } else {
        $pct = xhprof_percent_format($numer / abs($denom));
    }

    print("<td $attributes>$pct</td>\n");
}

/**
 * Print "flat" data corresponding to one function.
 *
 * @author Kannan
 */
function print_function_info($info) {
    static $odd_even = 0;
    global $totals;
    global $sort_col;
    global $metrics;
    global $format_cbk;

    // Toggle $odd_or_even
    $odd_even = 1 - $odd_even;

    if ($odd_even) {
        print("<tr>");
    }
    else {
        print('<tr>');
    }

    $href = xhp_run_url(array('symbol' => $info["fn"]));

    print('<td>');
    print(xhprof_render_link($info["fn"], $href).getBacktraceCallsForFunction($info["bcc"]));
    print("</td>\n");

    print_td_num($info["ct"], $format_cbk["ct"]);
    print_td_pct($info["ct"], $totals["ct"]);

    // Other metrics..
    foreach ($metrics as $metric) {
        // Inclusive metric
        print_td_num($info[$metric], $format_cbk[$metric],
            ($sort_col == $metric));
        print_td_pct($info[$metric], $totals[$metric],
            ($sort_col == $metric));

        // Exclusive Metric
        print_td_num($info["excl_" . $metric],
            $format_cbk["excl_" . $metric],
            ($sort_col == "excl_" . $metric));
        print_td_pct($info["excl_" . $metric],
            $totals[$metric],
            ($sort_col == "excl_" . $metric));
    }

    print("</tr>\n");
}

/**
 * Print non-hierarchical (flat-view) of profiler data.
 *
 * @author Kannan
 */
function print_flat_data($title, $flat_data, $limit, $callGraphButton) {

    global $stats;
    global $sortable_columns;

    $size  = count($flat_data);
    if (!$limit) {
        $limit = $size;
        $display_link = "";
    } else {
        $display_link = "<a href='" . xhp_run_url(array('all' => 1))
            . "' class='btn btn-sm btn-primary'>Display All</a>";
    }

    print('<div class="panel panel-default panel-functions">');

    print("<div class=\"panel-heading form-inline \"><h3 class=\"panel-title\" style='display:inline-block;'>$title</h3> ");
    SymbolSearchInputTemplate::render();
    echo "$display_link $callGraphButton";
    print("</div>");
    print('<table class="table table-functions table-condensed table-bordered">');
    \Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable\HeaderTemplate::render($stats, $sortable_columns);

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
    global $totals;
    global $metrics;
    global $descriptions;
    global $sort_col;
    global $sqlData;
    global $elasticData;
    global $unitSymbols;

    $possible_metrics = xhprof_get_possible_metrics();

    print("<p><center>\n");

    print('<table class="table table-bordered table-striped" style="width:auto;">' . "\n");
    echo "<tr>";
    echo "<th colspan='2' class='text-center'>Overall Summary</th>";
    echo "<th'></th>";
    echo "</tr>";

    foreach ($metrics as $metric) {
        echo "<tr>";
        echo "<td style='text-align:right; font-weight:bold'>Total "
            . str_replace("<br>", " ", stat_description($metric)) . ":</td>";
        echo "<td>" . number_format($totals[$metric]) . $possible_metrics[$metric][1] . "</td>";
        echo "</tr>";
    }

    if ($sqlData['count'] > 0) {
        echo "<tr>";
        echo "<td style='text-align:right; font-weight:bold'>Total SQL Queries Count:</td>";
        echo "<td>" . number_format($sqlData['count']) . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td style='text-align:right; font-weight:bold'>SQL Summary Time (". $unitSymbols['microsec'] ."):</td>";
        echo "<td>" . number_format($sqlData['time'] * 1E6) . $unitSymbols['microsec'] . "</td>";
        echo "</tr>";
    }

    if ($elasticData['count'] > 0) {
        echo "<tr>";
        echo "<td style='text-align:right; font-weight:bold'>Total Elastic Queries Count:</td>";
        echo "<td>" . number_format($elasticData['count']) . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td style='text-align:right; font-weight:bold'>Elastic Summary Time (". $unitSymbols['microsec'] ."):</td>";
        echo "<td>" . number_format($elasticData['time'] * 1E6) . $unitSymbols['microsec'] . "</td>";
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td style='text-align:right; font-weight:bold'>Number of Function Calls:</td>";
    echo "<td>" . number_format($totals['ct']) . "</td>";
    echo "</tr>";

    echo "</table>";
    print("</center></p>\n");

    $callgraph_report_title = '<i class="fa fa-pie-chart"></i> View Full Callgraph';

    \Sugarcrm\XHProf\Viewer\Templates\Run\SqlQueriesTableTemplate::render('SQL Queries', $sqlData, 'sql');
    \Sugarcrm\XHProf\Viewer\Templates\Run\QueriesTableTemplate::render('Elastic Queries', $elasticData, 'bash');

    $callGraphButton = '<a class="btn btn-primary btn-sm" target="_blank" href="' . xhp_callgraph_url() . '">'
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

//    print("<br>");

    if (!empty($url_params['all'])) {
        $all = true;
        $limit = 0;    // display all rows
    } else {
        $all = false;
        $limit = 100;  // display only limited number of rows
    }

    $desc = str_replace("<br>", " ", $descriptions[$sort_col]);

    if ($all) {
        $title = "Sorted by $desc";
    } else {
        $title = "Displaying top $limit functions: Sorted by $desc";
    }

    print_flat_data($title, $flat_data, $limit, $callGraphButton);
}

/**
 * Print info for a parent or child function in the
 * parent & children report.
 *
 * @author Kannan
 */
function pc_info($info, $base_ct, $base_info) {
    global $metrics;
    global $format_cbk;

    print_td_num($info["ct"], $format_cbk["ct"]);
    print_td_pct($info["ct"], $base_ct);

    /* Inclusive metric values  */
    foreach ($metrics as $metric) {
        print_td_num($info[$metric], $format_cbk[$metric]);
        print_td_pct($info[$metric], $base_info[$metric]);
    }
}

function print_pc_array($url_params, $results, $base_ct, $base_info, $parent) {
    global $metrics;

    // Construct section title
    if ($parent) {
        $title = 'Parent function';
    } else {
        $title = 'Child function';
    }

    if (count($results) > 1) {
        $title .= 's';
    }

    $columnsCount = count($metrics) * 2 + 1 + 2;

    print("<tr><td>");
    print("<b><i><center>" . $title . "</center></i></b>");
    print("</td><td colspan='$columnsCount'></td></tr>");

    $odd_even = 0;
    foreach ($results as $info) {
        $href = "?" . http_build_query(xhprof_array_set($url_params, 'symbol', $info["fn"]));
        $odd_even = 1 - $odd_even;

        if ($odd_even) {
            print('<tr>');
        } else {
            print('<tr>');
        }

        print("<td>" . xhprof_render_link($info["fn"], $href) . getBacktraceCallsForFunction($info["bcc"]). "</td>");
        pc_info($info, $base_ct, $base_info);
        print("</tr>");
    }
}

function print_symbol_summary($symbol_info, $stat, $base) {
    $val = $symbol_info[$stat];
    $desc = str_replace("<br>", " ", stat_description($stat));

    print("$desc: </td>");
    print(number_format($val));
    print(" (" . pct($val, $base) . "% of overall)");
    if (substr($stat, 0, 4) == "excl") {
        $func_base = $symbol_info[str_replace("excl_", "", $stat)];
        print(" (" . pct($val, $func_base) . "% of this function)");
    }
    print("<br>");
}

/**
 * Generate the profiler report for a single run.
 *
 * @author Kannan
 */
function profiler_single_run_report ($url_params, $xhprof_data, $rep_symbol, $sort, $run) {
    init_metrics($xhprof_data, $rep_symbol, $sort);
    profiler_report($url_params, $rep_symbol, $run, $xhprof_data);
}

function getBacktraceCallsForFunction($name)
{
    return "<td>$name</td>";
}

function xhp_run_url($params = array())
{
    global $run_page_params;
    return '?' . http_build_query(array_merge($run_page_params, $params));
}

function xhp_callgraph_url($params = array())
{
    global $run_page_params;
    return '?' . http_build_query(array_merge(
        array(
            'callgraph' => 1,
            'dir' => $run_page_params['dir'],
            'run' => $run_page_params['run'],
        ),
        $params
    ));
}
