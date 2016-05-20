<?php

namespace Sugarcrm\XHProf\Viewer\Storage;

/**
 * Class FileStorage
 */
class FileStorage extends AbstractStorage
{
    /**
     * @var string current directory key
     */
    protected $currentDirectory = '';

    /**
     * @var array List of available directories in the storage
     */
    protected $availableDirectories;

    /**
     * @var string Root of the data
     */
    protected $root;

    /**
     * Set the root of the data
     */
    public function __construct()
    {
        if (isset($GLOBALS['config']['profile_files_dir'])) {
            $this->root = $GLOBALS['config']['profile_files_dir'];
        } else {
            throw new \RuntimeException('`profile_files_dir` is not set');
        }
    }

    /**
     * @inheritdoc
     */
    public function listDirectories()
    {
        if (!$this->availableDirectories) {
            $dirs = array_map(function($item) {
                return basename($item);
            }, glob($this->root . '/*', GLOB_ONLYDIR));

            array_unshift($dirs, "");
            $dirs = array_combine($dirs, array_map(function($dirname) {
                return '/' . $dirname;
            }, $dirs));

            $this->availableDirectories = $dirs;
        }

        return $this->availableDirectories;
    }

    /**
     * @inheritdoc
     */
    public function getRunsList($params = array())
    {
        $total = 0;
        $bufFiles = array();
        foreach (glob($this->getCurrentDirectoryFullPath() . '/*') as $index => $file) {
            $pi = pathinfo($file);
            if (!empty($pi['extension']) && $pi['extension'] == 'xhprof') {
                $buf = $this->parseFilename($pi['filename']);
                $buf['run'] = $pi['filename'];

                $stat = stat($file);
                $buf['size'] = $stat['size'];

                $bufFiles[] = $buf;
                $total++;
            }
        }
        $grandTotal = $total;

        // apply filters
        foreach ($bufFiles as $index => $file) {
            if (!($file['timestamp'] >= $params['timestamp_from'] && $file['timestamp'] <= $params['timestamp_to'])) {
                unset($bufFiles[$index]);
            }
        }

        if (!empty($params['text'])) {
            foreach ($bufFiles as $index => $file) {
                if (stripos($file['namespace'], $params['text']) === false) {
                    unset($bufFiles[$index]);
                }
            }
        }

        if (!empty($params['wall_time_min'])) {
            foreach ($bufFiles as $index => $file) {
                if (!is_null($file['wall_time']) && $file['wall_time'] < $params['wall_time_min'] * 1E3) {
                    unset($bufFiles[$index]);
                }
            }
        }

        usort($bufFiles, function ($a, $b) use ($params) {
            return $params['sort_dir'] != 'desc'
                ? ($a[$params['sort_by']] > $b[$params['sort_by']])
                : ($a[$params['sort_by']] < $b[$params['sort_by']]);
        });

        $total = count($bufFiles);
        $files = array_slice($bufFiles, $params['offset'], $params['limit'], true);

        return array('runs' => $files, 'total' => $total, 'grand_total' => $grandTotal);
    }

    /**
     * @inheritdoc
     */
    public function getRunMetaData($run)
    {
        return $this->parseFilename($run);
    }

    /**
     * @inheritdoc
     */
    public function getRunXHProfData($run)
    {
        $xhprofData = $this->fileGetData($this->getRunFullPath($run));

        $sqlData = $this->getRunSqlData($run);
        $backtraceCalls = !empty($sqlData['backtrace_calls']) ? $sqlData['backtrace_calls'] : array();
        $this->updateXHProfDataWithBacktraceCalls($xhprofData, $backtraceCalls);

        return $xhprofData;
    }

    protected function updateXHProfDataWithBacktraceCalls(&$xhprofData, $backtraceCalls)
    {
        foreach($xhprofData as $k => $v) {
            $xhprofData[$k]['bcc'] = '';
            $kparts = explode('==>', $k);
            if (sizeof($kparts) == 2) {
                if (isset($backtraceCalls[$kparts[1]])) {
                    $xhprofData[$k]['bcc'] = $backtraceCalls[$kparts[1]];
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getRunSqlData($run, $params = array())
    {
        $result = array(
            'count' => 0,
            'queries' => array(),
        );

        $params = $params + array(
                'type' => 'all',
                'regex_text' => '',
                'sort_by' => 'time',
            );

        $runFullPath = $this->getRunFullPath($run);
        if ($sqlFileName = $this->determineSQLFileName($runFullPath)) {
            $data = $this->fileGetData($sqlFileName);

            if (isset($data['backtrace_calls'])) {
                $result['backtrace_calls'] = array();
                foreach ($data['backtrace_calls'] as $k => $v) {
                    $result['backtrace_calls'][str_replace('->', '::', $k)] = $v;
                }
            }

            $result['count'] = count($data['sql']);
            $result['time'] = $data['summary_time'];
            if (!empty($data['summary_fetch_time'])) {
                $result['fetch_time'] = $data['summary_fetch_time'];
            }

            $sqlTypeRegexMap = array(
                'select' => '/^\w*select/i',
                'modify' => '/^\w*(insert|update)/i'
            );

            foreach ($data['sql'] as $rowIndex => $row) {

                $sqlType = 'other';
                foreach ($sqlTypeRegexMap as $type => $regex) {
                    if (preg_match($regex, trim($row[0]))) {
                        $sqlType = $type;
                    }
                }

                if ($params['type'] != 'all' && $params['type'] != $sqlType) {
                    continue;
                }

                $matches = array();
                if ($params['regex_text']) {
                    if (preg_match_all(
                        '/' . $params['regex_text'] . '/' . $params['regex_mod'],
                        $row[0],
                        $matches,
                        PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE
                    )) {
                        $matches = array_map(function($match) {
                            return array($match[1], $match[1] + strlen($match[0]));
                        }, $matches[0]);
                    } else {
                        continue;
                    }
                }

                $sqlKey = $params['sort_by'] == 'exec_order' ? $rowIndex : md5($row[0]);
                $traceKey = md5($row[2]);
                if (!isset($result['queries'][$sqlKey])) {
                    $result['queries'][$sqlKey] = array(
                        'time' => 0,
                        'hits' => 0,
                        'traces' => array()
                    );
                }

                $result['queries'][$sqlKey]['hits']++;
                $result['queries'][$sqlKey]['time'] += $row[1];
                $result['queries'][$sqlKey]['query'] = $row[0];
                $result['queries'][$sqlKey]['fetch_count'] = isset($row[3]) ? $row[3] : 0;
                $result['queries'][$sqlKey]['fetch_time'] = isset($row[4]) ? $row[4] : 0;
                $result['queries'][$sqlKey]['highlight_positions'] = $matches;

                if (!isset($result['queries'][$sqlKey]['traces'][$traceKey])) {
                    $result['queries'][$sqlKey]['traces'][$traceKey] = array(
                        'hits' => 0,
                        'time' => 0
                    );
                }

                $row[2] = $this->handleSalesConnectStackTrace($row[2]);
                $result['queries'][$sqlKey]['traces'][$traceKey]['hits']++;
                $result['queries'][$sqlKey]['traces'][$traceKey]['time'] += $row[1];
                $result['queries'][$sqlKey]['traces'][$traceKey]['content'] = htmlspecialchars($row[2]);
                $result['queries'][$sqlKey]['traces'][$traceKey]['content_short']
                    = htmlspecialchars($this->shortenStackTrace($row[2]));
            }

            if ($params['sort_by'] != 'exec_order') {
                $sortCallback = function ($a, $b) use ($params) {
                    return $a[$params['sort_by']] < $b[$params['sort_by']];
                };

                usort($result['queries'], $sortCallback);
                foreach ($result['queries'] as $sqlKey => &$sqlData) {
                    usort($sqlData['traces'], $sortCallback);
                }
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRunElasticData($run)
    {
        $result = array(
            'count' => 0,
            'queries' => array()
        );

        $runFullPath = $this->getRunFullPath($run);
        if ($elasticFileName = $this->determineElasticFileName($runFullPath)) {
            $data = $this->fileGetData($elasticFileName);
            $data = $this->handleSalesConnectElasticDataFormat($data);
            $result['count'] = count($data['queries']);

            foreach ($data['queries'] as $query) {
                $result['queries'][] = array(
                    'query' => array($query[0], $query[1]),
                    'hits' => 1,
                    'time' => $query[2],
                    'traces' => array(
                        array(
                            'hits' => 1,
                            'time' => $query[2],
                            'content' => $query[3],
                            'content_short' => $this->shortenStackTrace($query[3])
                        ),
                    )
                );
            }
            $result['time'] = $data['summary_time'];
        }

        return $result;
    }

    /**
     * @return string Current directory full path
     */
    protected function getCurrentDirectoryFullPath()
    {
        return $this->root . (!empty($this->currentDirectory) ? '/' . $this->currentDirectory : '');
    }

    /**
     * @param $run
     * @return string Run full path
     */
    protected function getRunFullPath($run)
    {
        return $this->getCurrentDirectoryFullPath() . '/' . $run . '.xhprof';
    }

    /**
     * Parses the filename to get the run metadata
     *
     * @param $filename
     * @return array
     */
    protected function parseFilename($filename)
    {
        if (preg_match('/^(\d+)d(\d+)d(\d+)d(\d+)d(\d+)\.([^.]+)$/', $filename, $matches)) {
            return array(
                'timestamp' => floatval($matches[1] . '.' . $matches[2]),
                'wall_time' => $matches[3],
                'sql_queries' => $matches[4],
                'elastic_queries' => $matches[5],
                'namespace' => $matches[6]
            );
        }

        // Check of OOTB Sugar file format
        if (preg_match('/^([A-Za-z0-9]+)\.(\d+-\d+)-(.*)$/', $filename, $matches)) {
            return array(
                'timestamp' => floatval(str_replace('-', '.', $matches[2])),
                'wall_time' => null,
                'sql_queries' => null,
                'namespace' => $matches[3]
            );
        }

        $name_parts = explode('.', $filename);
        $shortname = array_shift($name_parts);
        $shortname .= '.' . array_shift($name_parts);
        $slq_queries = array_shift($name_parts);
        $wt = array_shift($name_parts);
        $namespace = implode('_', $name_parts);
        $buf = array(
            'timestamp' => floatval($shortname),
            'wall_time' => $wt,
            'sql_queries' => intval($slq_queries),
            'namespace' => $namespace
        );

        return $buf;
    }

    /**
     * Loads the data from the file
     *
     * @param $fileName
     * @return array
     */
    protected function fileGetData($fileName)
    {
        return unserialize(file_get_contents($fileName));
    }

    /**
     * Removes filenames from the trace
     *
     * @param $trace
     * @return string
     */
    protected function shortenStackTrace($trace)
    {
        return preg_replace('/^(#\d+).*\[Line: (\d+|n\/a)\]/m', '$1' ,$trace);
    }

    /**
     * Search for the file with sql queries data
     *
     * @param $runFileName
     * @return bool|string
     */
    protected function determineSQLFileName($runFileName)
    {
        if (is_file($runFileName . '.sql')) {
            return $runFileName . '.sql';
        }

        $sqlFileName = preg_replace('/\.xhprof$/', '.xhsql', $runFileName, 1);
        if (is_file($sqlFileName)) {
            return $sqlFileName;
        }

        return false;
    }

    /**
     * Search for the file with elastic queries data
     *
     * @param $runFileName
     * @return bool|string
     */
    protected function determineElasticFileName($runFileName)
    {
        if (is_file($runFileName . '.elastic')) {
            return $runFileName . '.elastic';
        }

        $sqlFileName = preg_replace('/\.xhprof$/', '.xhelastic', $runFileName, 1);
        if (is_file($sqlFileName)) {
            return $sqlFileName;
        }

        return false;
    }

    /**
     * Convert sales connect elastic data to the internally complied structure if applicable
     *
     * @param $data
     * @return array
     */
    protected function handleSalesConnectElasticDataFormat($data)
    {
        if (!empty($data['queries']) && $data['queries'][0] instanceof \IBMXHProfElastic\Query)  {
            $data['queries'] = array_map(function($query) {
                return array(
                    '',
                    json_decode($query->getQuery(), true),
                    $query->getTime() / 1E3,
                    $this->handleSalesConnectStackTrace($query->getBacktrace())
                );
            }, $data['queries']);
            $data['summary_time'] = $data['summary_time'] / 1E3;
        }

        return $data;
    }

    /**
     * Fixes spacing in the sales connect stack traces
     *
     * @param $stacktrace
     * @return string
     */
    protected function handleSalesConnectStackTrace($stacktrace)
    {
        return preg_replace('/^\s+(#\d+)/m', '$1', $stacktrace);
    }
}
