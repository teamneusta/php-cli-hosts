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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TeamNeusta\Hosts\Services\Provider\Filesystem;

/**
 * Class UpdateCommand
 * Only utilizing a given Library.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class ChangeUrlCommand extends Command
{
    private ?Filesystem $filesystem;

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
            ->setName('global:change')
            ->addOption('url', 'url', InputOption::VALUE_REQUIRED, 'Change global host file url.')
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
        $io = new SymfonyStyle($input, $output);
        $url = $input->getOption('url');
        if (!$url) {
            $io->error('no url provided.');
            return Command::FAILURE;
        }

        $this->filesystem->setGlobalHostsUrl($url, true);
        $io->info('no url provided.');

        return Command::SUCCESS;
    }
}