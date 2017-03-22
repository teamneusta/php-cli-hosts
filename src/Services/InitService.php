<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services;

use TeamNeusta\Hosts\Exception\HostAlreadySet;
use TeamNeusta\Hosts\Services\Provider\File;
use TeamNeusta\Hosts\Services\Provider\Filesystem;

/**
 * Class InitService
 * @package Neusta\Hosts\Services
 */
class InitService
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * InitService constructor.
     * @codeCoverageIgnore
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs = null)
    {
        $this->fs = $fs ?? new Filesystem();
    }

    /**
     * Check if .hosts file exist in
     * current user home directory.
     *
     * @return bool
     */
    public function localConfigurationExist() : bool
    {
        $homeDir = $this->fs->getHomeDir();

        return file_exists($homeDir . DIRECTORY_SEPARATOR . Filesystem::CONFIGURATION_FILE_NAME);
    }

    /**
     * Create local configuration file if not exist.
     * simply utilize existing method.
     *
     * @codeCoverageIgnore
     * Generate empty file.
     */
    public function createLocalConfiguration()
    {
        $this->fs->getLocalConfiguration();
    }

    /**
     * Passing parameters to Filesystem Provider.
     *
     * @codeCoverageIgnore
     * @param $globalHostsUrl
     */
    public function addGlobalHostUrl($globalHostsUrl, $override = false)
    {
        $this->fs->setGlobalHostsUrl($globalHostsUrl, $override);
    }
}