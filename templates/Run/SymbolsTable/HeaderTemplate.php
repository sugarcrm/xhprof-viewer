<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable;


use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;

class HeaderTemplate
{
    protected static $columns;

    protected static $exclColumns;

    public static function prepareColumns($xhprofDataEntry, $noExcl = false)
    {
        $exclData = array();
        if ($noExcl) {
            foreach ($xhprofDataEntry as $column => $info) {
                if (substr($column, 0, strlen('excl_')) == 'excl_') {
                    unset($xhprofDataEntry[$column]);
                    $exclData[$column] = $info;
                }
            }
        }

        $meta = array(
            'fn' => array(
                'cb' => function($fn) {
                    $href = CurrentPageHelper::url(array('symbol' => $fn));
                    return '<a href="' . $href . '">' . htmlspecialchars($fn) . '</a>';
                },
                'percentage' => false,
            ),
            'bcc' => array(
                'percentage' => function($info) {
                    return $info['queries'] ?? 0;
                },
                'cb' => 'xhprof_count_format',
            ),
            'ct' => array(
                'percentage' => true,
                'cb' => 'xhprof_count_format',
            ),
            'wt' => array(
                'percentage' => true,
            ),
            'excl_wt' => array(
                'percentage' => true,
                'total' => 'wt',
            ),
            'cpu' => array(
                'percentage' => true,
            ),
            'excl_cpu' => array(
                'percentage' => true,
                'total' => 'cpu',
            ),
            'mu' => array(
                'percentage' => true,
            ),
            'excl_mu' => array(
                'percentage' => true,
                'total' => 'mu',
            ),
            'pmu' => array(
                'percentage' => true,
            ),
            'excl_pmu' => array(
                'percentage' => true,
                'total' => 'pmu',
            ),
        );

        static::$columns = array_intersect_key($meta, $xhprofDataEntry);
        static::$exclColumns = array_intersect_key($meta, $exclData);
    }

    public static function getColumns()
    {
        return static::$columns;
    }

    public static function getExclColumns()
    {
        return static::$exclColumns;
    }

    public static function getColumnsCount()
    {
        return array_reduce(static::getColumns(), function($memo, $meta) {
            return $memo + (!empty($meta['percentage']) ? 2 : 1);
        }, 0);
    }

    public static function render()
    {
        ?>
        <thead>
            <tr>
                <?php foreach (static::getColumns() as $column => $meta) { ?>
                    <th <?php if (!empty($meta['percentage'])) { ?>colspan="2"<?php } ?>>
                        <a href="<?php echo CurrentPageHelper::url(array('sort' => $column)); ?>">
                            <?php echo stat_description($column); ?>
                            <?php if (CurrentPageHelper::getParam('sort') == $column) { ?>
                                <i class="fa fa-sort-amount-desc" aria-hidden="true"></i>
                            <?php } ?>
                        </a>
                    </th>
                <?php } ?>
            </tr>
        </thead>
        <?php
    }
}
