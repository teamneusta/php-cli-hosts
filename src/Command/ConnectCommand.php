<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Command;

use TeamNeusta\Hosts\Services\HostService;
use TeamNeusta\Hosts\Services\Provider\Cli;
use TeamNeusta\Hosts\Services\Validator\Scope;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectCommand extends AbstractCommand
{
    /**
     * Configure connect Command.
     */
    protected function configure()
    {
        $this
            ->setName('connect')
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_OPTIONAL,
                'Use local or global scope',
                null
            )
            ->setDescription('Get a list of availiable hosts');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
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
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $scope = $input->getOption('scope');
        Scope::validateScope($scope);

        $hostService = $this->_hostService;
        $hostService->setScope($scope);

        $hosts = $hostService->getHostsForQuestionhelper();
        $hosts[] = 'exit';
        $helper = new QuestionHelper();

        $question = new ChoiceQuestion(
            'Please select a host:',
            $hosts,
            0
        );
        $question->setErrorMessage('Host #%s is invalid.');

        $host = $helper->ask($input, $output, $question);

        if ($host == 'exit') {
            $output->writeln('exiting.');
            $output->writeln('have a nice day :-)');
            return 0;
        }

        $output->writeln('You have selected: ' . $host);
        $output->writeln("establishing connection...");
        $string = $hostService->getConnectionStringByName($host);

        $style = new SymfonyStyle($input, $output);
        $style->caution('Leaving local bash!');
        $this->_cli->passthruSsh($string);

        return 0;
    }
}