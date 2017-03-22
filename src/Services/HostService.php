<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services;

use TeamNeusta\Hosts\Services\Provider\Filesystem;

class HostService
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * scope to interact with.
     *
     * @var string
     */
    private $_scope = null;

    /**
     * HostService constructor.
     */
    public function __construct(Filesystem $fs = null)
    {
        $this->fs = $fs ?? new Filesystem();
    }

    /**
     * Add given configuration to .magehost file.
     *
     * @param $name
     * @param $host
     * @param $user
     * @param int $port
     */
    public function update($name, $host, $user, $port = 22)
    {
        $config = [
            'name' => $name,
            'host' => $host,
            'user' => $user,
            'port' => $port
        ];

        $this->fs->addHostToConfiguration($config, $this->_scope);
    }


    public function getHosts($scope) : array
    {
        switch ($scope) {
            case 'local':
                $config = $this->fs->getLocalConfiguration();
                break;
            case 'project':
                $config = $this->fs->getProjectConfiguration();
                break;
            default:
                $local = $this->fs->getLocalConfiguration();
                $project = $this->fs->getProjectConfiguration();
                $global = $this->fs->getGlobalConfiguration(); // temporary disabled.
                $config = $this->mergeConfigs($local, $project, $global);
                break;
        }
        return $config['hosts'];
    }

    private function mergeConfigs() : array
    {
        $args = func_get_args();
        $config = ['hosts' => []];
        foreach ($args as $hosts) {
            if (isset($hosts['hosts'])) {
                foreach ($hosts['hosts'] as $entry) {
                    $config['hosts'][] = $entry;
                }
            }
        }
        return $config;
    }

    public function getHostsForQuestionhelper() : array
    {
        $config = $this->getHosts($this->_scope);
        $hosts = [];
        foreach ($config as $host) {
            $hosts[] = $host['name'];
        }
        return $hosts;
    }

    public function getConnectionStringByName($host) : string
    {
        $config = $this->getHosts($this->_scope);

        $hostKey = array_search($host, array_column($config, 'name'));

        $sshCommand = $config[$hostKey]['user'] . '@' . $config[$hostKey]['host'] . ' -p ' . $config[$hostKey]['port'];

        return $sshCommand;
    }

    public function setScope($scope)
    {
        $this->_scope = $scope;
    }
}