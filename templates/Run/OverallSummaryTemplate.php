<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Run;


class OverallSummaryTemplate
{
    public static function render($possibleMetrics, $metrics, $totals, $sqlData, $elasticData, $unitSymbols)
    {
        ?>
        <table class="table table-bordered table-striped" style="width:auto;">
            <tr>
                <th colspan='2' class='text-center'>Overall Summary</th>
            </tr>
            <?php foreach ($metrics as $metric) { ?>
                <tr>
                    <td style='text-align:right; font-weight:bold'>Total
                        <?php echo str_replace("<br>", " ", stat_description($metric)) ?>:
                    </td>
                    <td>
                        <?php echo number_format($totals[$metric]) . $possibleMetrics[$metric][1] ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($sqlData['count'] > 0) { ?>
                <tr>
                    <td style='text-align:right; font-weight:bold'>Total SQL Queries Count:</td>
                    <td><?php echo number_format($sqlData['count']) ?></td>
                </tr>

                <tr>
                    <td style='text-align:right; font-weight:bold'>
                        SQL Summary Time (<?php echo $unitSymbols['microsec'] ?>):
                    </td>
                    <td>
                        <?php echo number_format($sqlData['time'] * 1E6) . $unitSymbols['microsec'] ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($elasticData['count'] > 0) { ?>
                <tr>
                    <td style='text-align:right; font-weight:bold'>Total Elastic Queries Count:</td>
                    <td><?php echo number_format($elasticData['count']) ?></td>
                </tr>

                <tr>
                    <td style='text-align:right; font-weight:bold'>Elastic Summary Time (<?php echo $unitSymbols['microsec'] ?>):</td>
                    <td><?php echo number_format($elasticData['time'] * 1E6) . $unitSymbols['microsec'] ?></td>
                </tr>
            <?php } ?>

            <tr>
                <td style='text-align:right; font-weight:bold'>Number of Function Calls:</td>
                <td><?php echo number_format($totals['ct']) ?></td>
            </tr>
        </table>
        <?php
    }
}
