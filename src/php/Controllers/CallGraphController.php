<?php

namespace Sugarcrm\XHProf\Viewer\Controllers;


class CallGraphController extends AbstractController
{
    public function indexAction()
    {
        ini_set('max_execution_time', 100);

        $params = array(// run id param
            'run' => array(XHPROF_STRING_PARAM, ''),

            // source/namespace/type of run
            'source' => array(XHPROF_STRING_PARAM, 'xhprof'),

            // the focus function, if it is set, only directly
            // parents/children functions of it will be shown.
            'func' => array(XHPROF_STRING_PARAM, ''),

            // image type, can be 'jpg', 'gif', 'ps', 'png'
            'type' => array(XHPROF_STRING_PARAM, 'png'),

            // only functions whose exclusive time over the total time
            // is larger than this threshold will be shown.
            // default is 0.01.
            'threshold' => array(XHPROF_FLOAT_PARAM, 0.01),

            // whether to show critical_path
            'critical' => array(XHPROF_BOOL_PARAM, true),

            // first run in diff mode.
            'run1' => array(XHPROF_STRING_PARAM, ''),

            // second run in diff mode.
            'run2' => array(XHPROF_STRING_PARAM, '')
        );

        // pull values of these params, and create named globals for each param
        xhprof_param_init($params);
        global $run, $source, $func, $type, $threshold, $critical, $run1, $run2, $xhprof_legal_image_types;

        // if invalid value specified for threshold, then use the default
        if ($threshold < 0 || $threshold > 1) {
            $threshold = $params['threshold'][1];
        }

        // if invalid value specified for type, use the default
        if (!array_key_exists($type, $xhprof_legal_image_types)) {
            $type = $params['type'][1]; // default image type.
        }

        if (!empty($run)) {
            // single run call graph image generation
            xhprof_render_image($this->storage, $run, $type,
                $threshold, $func, $source, $critical);
        }
    }
}
