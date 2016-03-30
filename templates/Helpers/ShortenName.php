<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Helpers;


class ShortenName
{
    public static function render($symbolName, $limit = 50)
    {
        if (strlen($symbolName) > $limit) { ?>
            <span data-toggle="tooltip" title="<?php echo htmlspecialchars($symbolName) ?>">
                <?php echo htmlspecialchars(substr($symbolName, 0, 10) . '...' . substr($symbolName, - $limit + 10)) ?>
            </span>
        <?php } else { ?>
            <?php echo htmlspecialchars($symbolName) ?>
        <?php }
    }
}
