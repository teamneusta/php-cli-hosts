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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends AbstractCommand
{
    /**
     * Configure host:add Command.
     */
    protected function configure()
    {
        $this
            ->setName('host:add')
            ->setAliases([
                'hosts:add'
            ])
            ->setDescription('interactively add new hosts');
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
        $helper = new QuestionHelper();

        $question = new Question('Please enter name: ');
        $name = $helper->ask($input, $output, $question);

        $question = new Question('Please enter host: ');
        $hostname = $helper->ask($input, $output, $question);

        $question = new Question('Please enter username: ');
        $user = $helper->ask($input, $output, $question);

        $question = new Question('Please enter Port:[22] ', 22);
        $port = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Please select a host:',
            [
                'local',
                'project'
            ],
            0
        );
        $question->setErrorMessage('Scope #%s is invalid.');
        $scope = $helper->ask($input, $output, $question);

        $this->_hostService->setScope($scope);
        $this->_hostService->update($name, $hostname, $user, $port);

        $output->writeln('Added Entry: ' . $user . '@' . $hostname . ' for ' . $scope . ' scope.');
        return 0;
    }
}