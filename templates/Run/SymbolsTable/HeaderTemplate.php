<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Run\SymbolsTable;


use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;

class HeaderTemplate
{
    public static function render($stats, $sortableColumns)
    {
        ?>
        <thead>
            <tr>
                <?php foreach ($stats as $stat) { ?>
                    <th>
                        <?php if (array_key_exists($stat, $sortableColumns)) { ?>
                            <a href="<?php echo CurrentPageHelper::url(array('sort' => $stat)); ?>">
                                <?php echo stat_description($stat); ?>
                            </a>
                        <?php } else { ?>
                            <?php echo stat_description($stat); ?>
                        <?php } ?>
                    </th>
                <?php } ?>
            </tr>
        </thead>
        <?php
    }
}
