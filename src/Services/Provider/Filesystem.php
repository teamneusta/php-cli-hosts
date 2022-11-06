<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services\Provider;

use TeamNeusta\Hosts\Exception\HostAlreadySetException;
use \Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use TeamNeusta\Hosts\Services\Validator\Scope;

/**
 * Class Filesystem
 *
 * @package TeamNeusta\Hosts\Services\Provider
 */
class Filesystem
{
    /**
     * configuration file name.
     */
    public const CONFIGURATION_FILE_NAME = '.hosts';

    /**
     * @var SymfonyFilesystem
     */
    protected $fs;

    /**
     * @var File
     */
    protected $file;

    /**
     * Determines if currently adding new Data to avoid setting scope.
     * Scope will only be added if not updating.
     *
     * @var bool
     */
    private $_isUpdate = false;

    /**
     * Minimal Configuration used to save and connect.
     *
     * @var array
     */
    private $_defaultConfig = [
        'name' => '',
        'host' => '',
        'user' => '',
        'port' => 22
    ];

    /**
     * Filesystem constructor.
     * @codeCoverageIgnore
     *
     * @param ?SymfonyFilesystem $fs
     * @param ?File $file
     */
    public function __construct(
        ?SymfonyFilesystem $fs = null,
        ?File $file = null
    )
    {
        $this->fs = $fs ?? new SymfonyFilesystem();
        $this->file = $file ?? new File();
    }

    /**
     * Retrieve home dir.
     *
     * @return null|string
     */
    public function getHomeDir(): ?string
    {
        // Cannot use _SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');
        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on Windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? NULL : $home;
    }

    /**
     * Retrieve local configuration.
     *
     * @param bool $override
     * @return array
     * @throws \IOException
     */
    public function getLocalConfiguration(bool $override = false) : array
    {
        $fileName = $this->getFilename(self::getHomeDir());
        $config = $this->getConfiguration($fileName);
        return $this->addScope($config, Scope::SCOPE_LOCAL);
    }

    /**
     * Retrieve project configuration.
     *
     * @param bool $createIfNotExist
     * @return array
     * @throws \IOException
     */
    public function getProjectConfiguration(bool $createIfNotExist = false) : array
    {
        $filename = $this->getFilename();
        $config = $this->getConfiguration($filename, $createIfNotExist);
        return $this->addScope($config, Scope::SCOPE_PROJECT);
    }

    /**
     * Retrieve local configuration.
     *
     * @return array
     * @throws \IOException
     */
    public function getGlobalConfiguration() : array
    {
        $url = $this->getGlobalUrlFromConfig();
        $config = [];
        if ($url !== null) {
            $config = $this->getConfigurationFromUrl($url);
            $config = $this->addScope($config, Scope::SCOPE_GLOBAL);
        }
        return $config;
    }

    /**
     * @param string $fileName
     * @param bool $createIfNotExist
     * @param bool $isUrl
     * @return array
     * @throws \IOException
     */
    public function getConfiguration(string $fileName, bool $createIfNotExist = true, bool $isUrl = false): array
    {
        if (!$this->fs->exists($fileName) && $createIfNotExist && !$isUrl) {
            try {
                // generate an empty array for local configuration
                $defaults = ['hosts' => []];
                $this->fs->dumpFile($fileName, json_encode($defaults));
            } catch (\Exception $e) {
                throw new \IOException($e->getMessage());
            }
        }
        $config = json_decode($this->file->getContents($fileName), true);
        if (is_null($config)) {
            return [];
        }
        return $config;
    }

    /**
     * Adds Host to configuration file.
     *
     * @param array $hostConfig
     * @param string $scope
     * @throws \Exception
     */
    public function addHostToConfiguration(array $hostConfig, string $scope = 'local'): void
    {
        try {
            $this->_isUpdate = true;
            switch ($scope) {
                case 'project':
                    $fileName = $this->getFilename();
                    $config = $this->getProjectConfiguration();
                    break;
                default:
                    $fileName = $this->getFilename(self::getHomeDir());
                    $config = $this->getLocalConfiguration();
                    break;
            }
            $config['hosts'][] = array_merge($this->_defaultConfig, $hostConfig);
            $this->fs->dumpFile($fileName, json_encode($config));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        } finally {
            $this->_isUpdate = false;
        }
    }

    /**
     * Dump given configuration to given path
     *
     * @codeCoverageIgnore
     * @param string $fileName
     * @param array $configuration
     * @return void
     */
    public function dumpGlobalConfiguration(string $fileName, array $configuration): void
    {
        $this->fs->dumpFile($fileName, json_encode($configuration));
    }

    /**
     * @param string $hostUrl
     * @param bool $override
     *
     * @throws HostAlreadySetException
     * @throws \IOException
     */
    public function setGlobalHostsUrl(string $hostUrl, bool $override = false): void
    {
        $fileName = $this->getFilename(self::getHomeDir());
        $config = $this->getConfiguration($fileName);

        if (isset($config['hosts_url']) && $override) {
            $config['hosts_url'] = $hostUrl;
        } elseif (isset($config['hosts_url'])) {
            throw new HostAlreadySetException($config['hosts_url']);
        } else {
            $config['hosts_url'] = $hostUrl;
        }
        $this->fs->dumpFile($fileName, json_encode($config));
    }

    /**
     * Get Filename with given location.
     *
     * @param string $baseDir
     * @return string
     */
    public function getFilename(string $baseDir = '.') : string
    {
        return $baseDir . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE_NAME;
    }

    /**
     * Add scope to each entry in config.
     *
     * @param $config
     * @param $scope
     *
     * @return mixed
     */
    public function addScope($config, $scope) : array
    {
        // do not add Scope during update. Scope will always be set when reading configuration.
        if (!$this->_isUpdate && is_array($config) && isset($config['hosts'])) {
            foreach ($config['hosts'] as $key => $entry) {
                $config['hosts'][$key]['scope'] = $scope;
            }
        }
        if (!is_array($config)) {
            $config = ['hosts' => []];
        }
        return $config;
    }

    /**
     * Get global URL from config.
     *
     * @return string|null
     * @throws \IOException
     */
    private function getGlobalUrlFromConfig() : ?string
    {
        $fileName = $this->getFilename(self::getHomeDir());
        $config = $this->getConfiguration($fileName, false);

        $hostUrl = null;
        if (isset($config['hosts_url'])) {
            $hostUrl = $config['hosts_url'];
        }

        return $hostUrl;
    }

    private function getConfigurationFromUrl(string $url): array
    {
        $configuration =  $this->getConfiguration(
            fileName: $url,
            isUrl: true

        );

        return $configuration;
    }
}