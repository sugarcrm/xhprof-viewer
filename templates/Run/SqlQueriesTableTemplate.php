<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Templates\Run;

use \Sugarcrm\XHProf\Viewer\Templates\Helpers\CurrentPageHelper as CurrentPage;

class SqlQueriesTableTemplate extends QueriesTableTemplate
{
    public static function renderTopButtons()
    {
        ?>
        <form method="get" class="form-inline" style="display:inline-block; margin:0">
            <?php foreach(CurrentPage::getParams() as $key => $value) { ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key) ?>" value="<?php echo htmlspecialchars($value) ?>"/>
            <?php } ?>

            <div class="input-group input-group-sm">
                <div class="input-group-addon">/</div>
                <input type="text" class="form-control" name="regex_text" style="width: 350px" placeholder="RegEx"
                       value="<?php echo htmlspecialchars(CurrentPage::getParam('regex_text')) ?>">
                <div class="input-group-addon" style="border-left: 0; border-right: 0;">/</div>
                <input type="text" class="form-control" name="regex_mod" style="width: 50px" placeholder=""
                       value="<?php echo htmlspecialchars(CurrentPage::getParam('regex_mod')) ?>">
            </div>

            <div class="btn-group" role="group">
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('type') == 'all' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('type' => 'all')) ?>">All Queries</a>
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('type') == 'select' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('type' => 'select')) ?>">Selects</a>
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('type') == 'modify' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('type' => 'modify')) ?>">Inserts/Updates</a>
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('type') == 'other' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('type' => 'other')) ?>">Others</a>
            </div>

            <div class="btn-group" role="group">
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('sort_by') == 'time' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('sort_by' => 'time')) ?>">Sort by Time</a>
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('sort_by') == 'hits' ? 'active' : '' ?>"
                   href="<?php echo CurrentPage::url(array('sort_by' => 'hits')) ?>">Sort by Hits</a>
                <a class="btn btn-primary btn-sm <?php echo CurrentPage::getParam('sort_by') == 'exec_order' ? 'active' : '' ?>"
                   data-toggle="tooltip" title="Sort in Execution Order"
                   href="<?php echo CurrentPage::url(array('sort_by' => 'exec_order')) ?>">Exec. Order</a>
            </div>

            <input type="submit" style="display:none;"/>
        </form>

        <?php
    }

    public static function renderQueryButtons()
    {
        ?>
        <button class="btn btn-default btn-xs btn-format-sql" type="button" data-toggle="tooltip" title="Pretty print">
            { }
        </button>
        <?php

        parent::renderQueryButtons();
    }

}
