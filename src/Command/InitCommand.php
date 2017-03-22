<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Command;

use TeamNeusta\Hosts\Exception\HostAlreadySet;
use TeamNeusta\Hosts\Exception\HostAlreadySetException;
use TeamNeusta\Hosts\Services\InitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class InitCommand
 * not implemented yet.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class InitCommand extends Command
{
    /**
     * @var InitService
     */
    protected $_initService;

    /**
     * Constructor.
     * @codeCoverageIgnore
     *
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @param InitService $initService
     */
    public function __construct($name = null, InitService $initService = null)
    {
        parent::__construct($name);
        if (is_null($initService)) {
            $initService = new InitService();
        }
        $this->_initService = $initService;
    }

    /**
     * Announce name and description so command could be called.
     *
     * @codeCoverageIgnore
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('init hosts');
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
        $output->writeln("Still work in Progress!");
        return 0;
    }
}