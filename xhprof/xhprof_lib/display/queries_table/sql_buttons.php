<form method="get" class="form-inline" style="display:inline-block; margin:0">
    <?php foreach($run_page_params as $key => $value) { ?>
        <input type="hidden" name="<?php echo $key ?>" value="<?php echo htmlentities($value) ?>"/>
    <?php } ?>

    <div class="input-group input-group-sm">
        <div class="input-group-addon">/</div>
        <input type="text" class="form-control" name="sql_regex_text" style="width: 350px" placeholder="RegEx"
               value="<?php echo htmlentities($run_page_params['sql_regex_text']) ?>">
        <div class="input-group-addon" style="border-left: 0;border-right: 0;">/</div>
        <input type="text" class="form-control" name="sql_regex_mod" style="width: 50px" placeholder=""
               value="<?php echo htmlentities($run_page_params['sql_regex_mod']) ?>">
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

    <div class="btn-group" role="group">
        <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_sort_by'] == 'time' ? 'active' : '' ?>"
           href="<?php echo xhp_run_url(array('sql_sort_by' => 'time')) ?>">Sort by Time</a>
        <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_sort_by'] == 'hits' ? 'active' : '' ?>"
           href="<?php echo xhp_run_url(array('sql_sort_by' => 'hits')) ?>">Sort by Hits</a>
        <a class="btn btn-primary btn-sm <?php echo $run_page_params['sql_sort_by'] == 'exec_order' ? 'active' : '' ?>"
           data-toggle="tooltip" title="Sort in Execution Order"
           href="<?php echo xhp_run_url(array('sql_sort_by' => 'exec_order')) ?>">Exec. Order</a>
    </div>

    <input type="submit" style="display:none;"/>
</form>
