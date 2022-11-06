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

/**
 * Class UpdateCommand
 * Only utilizing a given Library.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class InfoCommand extends Command
{
    private Filesystem $filesystem;

    private HostService $hostService;

    public function __construct(
        ?Filesystem $filesystem = null,
        ?HostService $hostService = null,
        string $name = null
    ) {
        parent::__construct($name);
        if (is_null($filesystem)) {
            $this->filesystem = new Filesystem();
        }
        if (is_null($hostService)) {
            $this->hostService = new HostService();
        }
    }

    /**
     * Announce name and description so command could be called.
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('global:info')
            // the short description shown while running "php bin/console list"
            ->setDescription('List global hosts file data.');
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
        }catch (\IOException $exception){
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        
        $table = new Table($output);

        $tableData = [];
        $hosts = $currentConfiguration['hosts'] ?? [];

        $io->writeln(sprintf('<fg=green>Version:</> <fg=yellow>%s</>', $currentConfiguration['version'] ?? 0));
        $io->writeln(sprintf('<fg=green>Last updated:</> <fg=yellow>%s</>',
            (new \DateTime())->setTimestamp($currentConfiguration['updated_at']?? -1)->format('d.m.Y H:i:s')));

        if (count($hosts) > 0) {
            foreach ($hosts as $host) {
                $tableData[] = [
                    $host['project'],
                    $host['name'],
                    $host['host'],
                    $host['port'],
                    $host['user'],
                    $host['added']
                ];
            }
        } else {
            $tableData[] = array(new TableCell('No entries found.', array('colspan' => 5)));
        }

        $table
            ->setHeaders(['Project', 'Name', 'Host', 'Port', 'User', 'Added'])
            ->setRows($tableData);
        $table->render();

        return Command::SUCCESS;
    }
}