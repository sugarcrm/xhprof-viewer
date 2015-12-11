<?php


require_once $GLOBALS['XHPProfLibRoot'].'/utils/xhprof_lib.php';

// param name, its type, and default value
$params = array('q'          => array(XHPROF_STRING_PARAM, ''),
                'run'        => array(XHPROF_STRING_PARAM, ''),
                'run1'       => array(XHPROF_STRING_PARAM, ''),
                'run2'       => array(XHPROF_STRING_PARAM, ''),
                'source'     => array(XHPROF_STRING_PARAM, 'xhprof'),
                );

// pull values of these params, and create named globals for each param
xhprof_param_init($params);

global $q, $run, $run1, $run2, $source;

if (!empty($run)) {

  // single run mode
  $raw_data = $xhprof_runs_impl->get_run($run, $source, $desc_unused);
  $functions = xhprof_get_matching_functions($q, $raw_data);

} else if (!empty($run1) && !empty($run2)) {

  // diff mode
  $raw_data = $xhprof_runs_impl->get_run($run1, $source, $desc_unused);
  $functions1 = xhprof_get_matching_functions($q, $raw_data);

  $raw_data = $xhprof_runs_impl->get_run($run2, $source, $desc_unused);
  $functions2 = xhprof_get_matching_functions($q, $raw_data);


  $functions = array_unique(array_merge($functions1, $functions2));
  asort($functions);
} else {
  xhprof_error("no valid runs specified to typeahead endpoint");
  $functions = array();
}

// If exact match is present move it to the front
if (in_array($q, $functions)) {
  $old_functions = $functions;

  $functions = array($q);
  foreach ($old_functions as $f) {
    // exact match case has already been added to the front
    if ($f != $q) {
      $functions[] = $f;
    }
  }
}

header('Content-Type: application/json');
echo json_encode(array_values($functions));
