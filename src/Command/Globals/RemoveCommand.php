<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Command\Globals;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TeamNeusta\Hosts\Services\HostService;
use TeamNeusta\Hosts\Services\Provider\Filesystem;
use TeamNeusta\Hosts\Services\Validator\Scope;
use TeamNeusta\Hosts\Services\Versioning;

/**
 * Class UpdateCommand
 * Only utilizing a given Library.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class RemoveCommand extends Command
{
    private Filesystem $filesystem;

    public function __construct(
        ?Filesystem $filesystem = null,
        string $name = null
    ) {
        parent::__construct($name);
        if (is_null($filesystem)) {
            $this->filesystem = new Filesystem();
        }
    }

    /**
     * Announce name and description so command could be called.
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('global:remove')
            // the short description shown while running "php bin/console list"
            ->setDescription('Remove Entry from global list.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $currentConfiguration = $this->filesystem->getGlobalConfiguration();
        } catch (\IOException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        if (!array_key_exists('hosts', $currentConfiguration)) {
            $io->info('No hosts configured yet.');
            return Command::SUCCESS;
        }

        if (!is_iterable($currentConfiguration['hosts'])) {
            $io->error('invalid hosts provided.');
            return Command::FAILURE;
        }

        if (count($currentConfiguration['hosts']) == 0) {
            $io->info('No hosts configured yet.');
            return Command::SUCCESS;
        }

        $hosts = array_map(function ($host) {
            return sprintf('%s (%s)', $host['name'], $host['project']);
        }, $currentConfiguration['hosts']);

        $hostToDelete = $io->choice(
            'Please select a host to delete',
            $hosts
        );
        $currentConfiguration['hosts'] = $this->removeHost($hostToDelete, $currentConfiguration['hosts']);

        $fileName = $io->ask('Filename to dump new configuration (relative to current path)', '.hosts.global');

        $currentConfiguration['version'] = Versioning::updateVersion($currentConfiguration['version']);
        $currentConfiguration['updated_at'] = (new \DateTime())->getTimestamp();

        $this->filesystem->dumpGlobalConfiguration($fileName, $currentConfiguration);

        return Command::SUCCESS;
    }

    private function removeHost(mixed $hostToDelete, array $hosts): array
    {
        preg_match("/(?<hostname>.*) \((?<project>.*)\)/", $hostToDelete, $matches);
        $hostname = $matches['hostname'] ?? false;
        $project = $matches['project'] ?? false;

        if($hostname !== false && $project !== false){
            $entry = array_filter($hosts, function ($host) use ($hostname, $project){
                return $host['name'] == $hostname && $host['project'] == $project;
            });
            if(count($entry) == 1){
                $key = array_key_first($entry);
                unset($hosts[$key]);

            }
        }
        return $hosts;
    }
}