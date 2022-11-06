<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services;

use TeamNeusta\Hosts\Exception\HostAlreadySetException;
use TeamNeusta\Hosts\Exception\ConfigurationAlreadyExistException;
use TeamNeusta\Hosts\Services\Provider\Filesystem;
use TeamNeusta\Hosts\Services\Validator\Scope;

/**
 * Class InitService
 * @package Neusta\Hosts\Services
 */
class InitService
{
    /**
     * @var Filesystem
     */
    private Filesystem $fs;

    /**
     * InitService constructor.
     * @codeCoverageIgnore
     * @param ?Filesystem $fs
     */
    public function __construct(Filesystem $fs = null)
    {
        $this->fs = $fs ?? new Filesystem();
    }

    /**
     * Create configuration for given scope if file not exist.
     *
     * @param string $scope
     * @return void
     *
     * @throws ConfigurationAlreadyExistException
     * @throws \IOException
     */
    public function createConfigurationByScope(string $scope): void
    {
        switch ($scope) {
            case Scope::SCOPE_PROJECT:
                $this->createProjectConfiguration(true);
                break;
            case Scope::SCOPE_GLOBAL:
                throw new \InvalidArgumentException(sprintf('scope "%s" is not available for direct updates.', $scope));
                break;
            default:
                $this->createLocalConfiguration(true);
        }
    }

    /**
     * Create local configuration file if not exist.
     * simply utilize existing method.
     *
     * @codeCoverageIgnore
     * Generate empty file.
     *
     * @param bool $createIfNotExist
     * @throws ConfigurationAlreadyExistException
     * @throws \IOException
     */
    public function createLocalConfiguration(bool $createIfNotExist = false): void
    {
        if ($this->configurationExist(Scope::SCOPE_LOCAL)) {
            throw new ConfigurationAlreadyExistException(Scope::SCOPE_LOCAL);
        }
        $this->fs->getLocalConfiguration($createIfNotExist);
    }

    /**
     * Create local configuration file if not exist.
     * simply utilize existing method.
     *
     * @codeCoverageIgnore
     * Generate empty file.
     *
     * @param bool $override
     * @throws ConfigurationAlreadyExistException
     * @throws \IOException
     */
    public function createProjectConfiguration(bool $createIfNotExist = false): void
    {
        if ($this->configurationExist(Scope::SCOPE_PROJECT)) {
            throw new ConfigurationAlreadyExistException(Scope::SCOPE_PROJECT);
        }
        $this->fs->getProjectConfiguration($createIfNotExist);
    }

    /**
     * Passing parameters to Filesystem Provider.
     *
     * @codeCoverageIgnore
     * @param string $globalHostsUrl
     * @param bool $override
     *
     * @throws \IOException
     * @throws HostAlreadySetException
     */
    public function addGlobalHostUrl(string $globalHostsUrl, bool $override = false): void
    {
        $this->fs->setGlobalHostsUrl($globalHostsUrl, $override);
    }

    /**
     * Check if .hosts file exist in
     * current user home directory.
     *
     * @param string $scope
     * @return bool
     */
    private function configurationExist(string $scope): bool
    {
        if ($scope == Scope::SCOPE_LOCAL){
            $homeDir = $this->fs->getHomeDir();
        } else {
            $homeDir = getcwd();
        }

        return file_exists($homeDir . DIRECTORY_SEPARATOR . Filesystem::CONFIGURATION_FILE_NAME);
    }
}