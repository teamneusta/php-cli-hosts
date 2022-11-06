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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TeamNeusta\Hosts\Console\ConsoleStyle;
use TeamNeusta\Hosts\Services\HostService;
use TeamNeusta\Hosts\Services\Provider\Filesystem;
use TeamNeusta\Hosts\Services\Versioning;

/**
 * Class UpdateCommand
 * Only utilizing a given Library.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class UpdateCommand extends Command
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
            ->setName('global:update')
            ->addOption('filename', 'filename', InputOption::VALUE_OPTIONAL, 'outputs configuration to file')
            // the short description shown while running "php bin/console list"
            ->setDescription('Change global hosts file url.');
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
        $fileName = $input->getOption('filename');
        $io = new ConsoleStyle($input, $output);
        try {
            $currentConfiguration = $this->filesystem->getGlobalConfiguration();
        } catch (\IOException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $hosts = $currentConfiguration['hosts'] ?? [];

        if (!array_key_exists('projects', $currentConfiguration)) {
            $project = $io->ask(
                'Please add a project'
            );
        } else {
            $currentConfiguration['projects'][] = 'add project';
            $project = $io->choice(
                'Please select a project',
                $currentConfiguration['projects']
            );
            if ($project == 'add project') {
                $project = $io->ask(
                    'Please enter a project name'
                );
                $currentConfiguration['projects'][] = $project;
            }
        }

        $currentConfiguration['projects'] = array_filter(
            $currentConfiguration['projects'],
            function ($project) {
                return $project != 'add project';
            }
        );

        $isFirstField = true;
        while (true) {
            $io->writeln('');
            if ($isFirstField) {
                $question = 'Please enter a descriptive name';
            } else {
                $question = sprintf(
                    'Add another host for %s ? Enter a name (or press <return> to stop adding fields)',
                    $project
                );
            }

            $name = $io->ask($question, null, function($name) use($project, $hosts){
                $keys = array_keys(array_column($hosts,'project'), $project);
                if(count($keys) > 0){
                    foreach ($keys as $key) {
                        if($hosts[$key]['name'] == $name) {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    'There is already an Entry with "%s" for Project "%s"',
                                    $name,
                                    $project
                                )
                            );
                        }
                    }
                }
                return $name;
            });

            if (null === $name) {
                break;
            }

            $question = 'Please enter a hostname/ip';
            $hostname = $io->ask($question, null, function ($hostname) {
                if (strlen(trim($hostname)) == 0) {
                    throw new \InvalidArgumentException('Hostname cannot be empty.');
                }
                return $hostname;
            });

            $question = 'Please enter a username';
            $user = $io->ask($question, null, function ($username) {
                if (strlen(trim($username)) == 0) {
                    throw new \InvalidArgumentException('Username cannot be empty.');
                }
                return $username;
            });

            $question = 'Please enter a Port';
            $port = $io->ask($question, 22);
            $isFirstField = false;

            $newHosts[] = [
                'project' => $project,
                'name' => $name,
                'host' => $hostname,
                'port' => (int)$port,
                'user' => $user,
                'added' => (int)(new \DateTime())->getTimestamp(),
            ];
        }


        $table = new Table($output);

        $tableData = [];


        $io->writeln('Entries to be added to global hosts configuration');
        foreach ($newHosts as $host) {
            $tableData[] = [
                $host['project'],
                $host['name'],
                $host['host'],
                $host['port'],
                $host['user']
            ];
        }

        $table
            ->setHeaders(['Project', 'Name', 'Host', 'Port', 'User'])
            ->setRows($tableData);

        $table->render();

        if (!$fileName) {
            $fileName = $io->ask('Filename to dump new configuration (relative to current path)', '.hosts.global');
        }

        $currentConfiguration['hosts'] = array_merge($hosts,$newHosts);
        $currentConfiguration['version'] = Versioning::updateVersion($currentConfiguration['version']);
        $currentConfiguration['updated_at'] = (new \DateTime())->getTimestamp();

        $this->filesystem->dumpGlobalConfiguration($fileName, $currentConfiguration);

        return Command::SUCCESS;
    }
}