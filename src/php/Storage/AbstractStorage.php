<?php

namespace Sugarcrm\XHProf\Viewer\Storage;

/**
 * Class AbstractStorage
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var string key of the current directory
     */
    protected $currentDirectory;

    /**
     * @inheritdoc
     */
    public function setCurrentDirectory($directory)
    {
        $directories = $this->listDirectories();
        if (isset($directories[$directory])) {
            $this->currentDirectory = $directory;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCurrentDirectory()
    {
        return $this->currentDirectory;
    }

    /**
     * @inheritdoc
     */
    public function getRunXHprofMatchingFunctions($run, $q)
    {
        $rawData = $this->getRunXHProfData($run);
        $functions = xhprof_get_matching_functions($q, $rawData);

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

        $functions = array_slice($functions, 0, 15);
        $functions = array_map(function($value) {
            return array('value' => $value);
        }, $functions);

        return array_values($functions);
    }
}
