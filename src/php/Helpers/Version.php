<?php

namespace Sugarcrm\XHProf\Viewer\Helpers;

/**
 * Version Helper
 */
class Version
{
    /**
     * Returns array of current version type and version
     *
     * @return array|bool
     */
    public function getCurrentVersion()
    {
        global $config;
        $version = $config['version'];

        if ($version == 'VIEWER_VERSION') {
            return $this->getHeadHash();
        } else {
            return array('type' => 'version', 'version' => $version);
        }
    }

    /**
     * Returns git HEAD hash as version
     *
     * @return array|bool
     */
    protected function getHeadHash()
    {
        $output = exec('git rev-parse --verify HEAD', $outputLines, $code);
        if ($code) {
            return false;
        }

        return array('type' => 'hash', 'version' => $output);
    }
}
