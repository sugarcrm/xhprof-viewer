<div class="btn-group" role="group">
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_sort_by'] == 'time' ? 'active' : '' ?>"
       href="<?php echo xhp_run_url(array('sql_sort_by' => 'time')) ?>">Sort by Time</a>
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_sort_by'] == 'hits' ? 'active' : '' ?>"
       href="<?php echo xhp_run_url(array('sql_sort_by' => 'hits')) ?>">Sort by Hits</a>
</div>

<div class="btn-group" role="group">
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_type'] == 'all' ? 'active' : '' ?>"
        href="<?php echo xhp_run_url(array('sql_type' => 'all')) ?>">All Queries</a>
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_type'] == 'select' ? 'active' : '' ?>"
       href="<?php echo xhp_run_url(array('sql_type' => 'select')) ?>">Selects</a>
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_type'] == 'modify' ? 'active' : '' ?>"
       href="<?php echo xhp_run_url(array('sql_type' => 'modify')) ?>">Inserts/Updates</a>
    <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_type'] == 'other' ? 'active' : '' ?>"
       href="<?php echo xhp_run_url(array('sql_type' => 'other')) ?>">Others</a>
</div>
