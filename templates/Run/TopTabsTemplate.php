<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Run;


use Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\FormatHelper;
use Sugarcrm\XHProf\Viewer\Templates\Helpers\UrlHelper;

class TopTabsTemplate
{
    public static function render($runData)
    {
        ?>
        <ul class="nav nav-tabs">
            <li role="presentation" <?php if (!CurrentPageHelper::getParam('page')) { ?>class="active"<?php } ?>>
                <a href="<?php echo UrlHelper::url(array(
                    'dir' => CurrentPageHelper::getParam('dir'),
                    'run' => CurrentPageHelper::getParam('run'),
                    'list_url' => CurrentPageHelper::getParam('list_url'),
                )) ?>">Function Calls</a>
            </li>
            <li role="presentation" <?php if (CurrentPageHelper::getParam('page') == 'sql') { ?>class="active"<?php } ?>>
                <a href="<?php echo UrlHelper::url(array(
                    'dir' => CurrentPageHelper::getParam('dir'),
                    'run' => CurrentPageHelper::getParam('run'),
                    'page' => 'sql',
                    'list_url' => CurrentPageHelper::getParam('list_url'),
                )) ?>">SQL Queries
                    <?php if (isset($runData['sql_queries'])) { ?>
                        <span class="badge"><?php echo FormatHelper::number($runData['sql_queries']) ?></span>
                    <?php } ?>
                </a>
            </li>
            <li role="presentation" <?php if (CurrentPageHelper::getParam('page') == 'elastic') { ?>class="active"<?php } ?>>
                <a href="<?php echo UrlHelper::url(array(
                    'dir' => CurrentPageHelper::getParam('dir'),
                    'run' => CurrentPageHelper::getParam('run'),
                    'page' => 'elastic',
                    'list_url' => CurrentPageHelper::getParam('list_url'),
                )) ?>">Elastic Queries
                    <?php if (isset($runData['elastic_queries'])) { ?>
                        <span class="badge"><?php echo FormatHelper::number($runData['elastic_queries']) ?></span>
                    <?php } ?>
                </a>
            </li>
        </ul>
        <?php
    }
}
