<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Common\Html;


class HeadTemplate
{
    public static function render($title, $csss, $jss)
    {
        ?>

        <head>
            <meta charset="utf-8">
            <title><?php echo htmlspecialchars($title) ?></title>
            <link rel="shortcut icon" type="image/png" href="xhprof/images/guitarist-309806_640.png"/>

            <?php foreach ($csss as $css) { ?>
                <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($css) ?>" />
            <?php } ?>

            <?php foreach ($jss as $js) { ?>
                <script type="text/javascript" src="<?php echo htmlspecialchars($js) ?>"></script>
            <?php } ?>
        </head>

        <?php
    }
}
