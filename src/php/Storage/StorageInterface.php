<?php
/**
 * © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\XHProf\Viewer\Storage;

/**
 * Interface StorageInterface
 */
interface StorageInterface
{
    /**
     * Returns list of directories available in the storage in the following format:
     * array('{directory key}' => '{directory name}', [...])
     *
     * @return array
     */
    public function listDirectories();

    /**
     * Set current directory
     *
     * @param string $directory Directory key
     * @return void
     */
    public function setCurrentDirectory($directory);

    /**
     * Returns list of runs available in the current directory
     *
     * The return array has the following structure:
     * <pre>
     * array(
     *  'runs' => array('{run number}' => {run}, [...])
     *  'total' => {amount of filtered runs}
     *  'grand_total' => {amount of all runs}
     * )
     * </pre>
     *
     * Where {run} has the following structure:
     * <pre>
     * array(
     *  'elastic_queries' => {amount of elastic queries},
     *  'namespace' => '{namespace of the run}',
     *  'run' => '{run id}',
     *  'size' => {size of profile data},
     *  'sql_queries' => {amount of sql queries},
     *  'timestamp' => {timestamp of the request},
     *  'wall_time' => {time that took to execute the request (in microseconds)}
     * )
     * </pre>
     *
     * @param array $params List of params: <br/>
     * timestamp_from - timestamp value to filter from <br/>
     * timestamp_to - timestamp value to filter to  <br/>
     * text - text that a run must include in it's name <br/>
     * wall_time_min - minimal wall time of a run (in milliseconds) <br/>
     * sort_by - on of (ts, wt, fs, sql) <br/>
     * sort_dir - on of (desc, asc) <br/>
     * offset - result set offset <br/>
     * limit - count of results to be returned <br/>
     *
     * @return array
     */
    public function getRunsList($params = array());

    /**
     * Returns metadata of the run
     *
     * The return array has the following structure:
     *
     *<pre>
     * array(
     *  'elastic_queries' => {amount of elastic queries},
     *  'namespace' => '{namespace of the run}',
     *  'sql_queries' => {amount of sql queries},
     *  'timestamp' => {timestamp of the request},
     *  'wall_time' => {time that took to execute the request (in microseconds)}
     * )
     * </pre>
     *
     * @param $run
     * @return array
     */
    public function getRunMetaData($run);

    /**
     * Returns the xhprof data of the run
     *
     * @param $run
     * @return array
     */
    public function getRunXHProfData($run);

    /**
     * Returns sql data of the run
     *
     * The return array has the following structure:
     * <pre>
     * array(
     *  'count' => {amount of sql queries},
     *  'time' => {summary sql time (in seconds)},
     *  'queries' => array({query},[...]),
     *  'backtrace_calls' => array('{function}' => {amount of time query was executed from this function}, [...]),
     * )
     * </pre>
     *
     * Where {query} has the follwing structure:
     * <pre>
     * array(
     *  'query' => '{query text}' | array('{query text}', array({query data}),
     *  'hits' => {amount of times the query was executed},
     *  'time' => {summary query time (in seconds)},
     *  'highlight_positions' => array(array({selection begin index}, {selection end index}),[...]),
     *  'traces' => array(
     *    array(
     *      'content' => '{trace full content (with files)}',
     *      'content_short' => '{short trace content (without files)}',
     *      'hits' => {number of time the query was executed from this trace},
     *      'time' => {summ time that took to execute the query from this trace (in seconds)},
     *    ), [...]
     *  )
     * )
     * </pre>
     *
     * @param $run
     * @param array $params List of params: <br/>
     * type - one of (all, select, modify, other) <br/>
     * regex_text - regular expression to filter queries <br/>
     * regex_mod - regular expression modifiers <br/>
     * sort_by - on of (time, hits)
     * @return array
     */
    public function getRunSqlData($run, $params = array());

    /**
     * Returns elastic data of the run
     *
     * For the return array structure please refere {@see \Sugarcrm\XHProf\Viewer\Storage\StorageInterface::getRunSqlData}
     *
     * @param $run
     * @return array
     */
    public function getRunElasticData($run);

    /**
     * Returns the array of functions that match $q
     *
     * The return array has the following structure:
     * <pre>
     * array(
     *  array('value' => '{function name}'),
     *  [...]
     * )
     * </pre>
     *
     * @param $run
     * @param $q
     * @return array
     */
    public function getRunXHprofMatchingFunctions($run, $q);
}
