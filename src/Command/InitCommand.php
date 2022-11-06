<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use TeamNeusta\Hosts\Exception\ConfigurationAlreadyExistException;
use TeamNeusta\Hosts\Services\InitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TeamNeusta\Hosts\Services\Provider\Filesystem;
use TeamNeusta\Hosts\Services\Validator\Scope;

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
    protected InitService $initService;

    /**
     * Constructor.
     * @codeCoverageIgnore
     *
     * @param ?string $name The name of the command; passing null means it must be set in configure()
     *
     * @param ?InitService $initService
     */
    public function __construct(
        string $name = null,
        InitService $initService = null
    ) {
        parent::__construct($name);
        if (is_null($initService)) {
            $initService = new InitService();
        }
        $this->initService = $initService;
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
            ->setDescription('init hosts')
            ->addArgument('scope', InputArgument::OPTIONAL, 'scope to be initialized');
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
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln("starting Initialization");
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $scope = $input->getArgument('scope');

        if (Scope::validateScope($scope)) {
            $question = new ChoiceQuestion(
                'choose scope to init',
                [
                    Scope::SCOPE_LOCAL,
                    Scope::SCOPE_PROJECT
                ],
                0
            );
            $scope = $questionHelper->ask(
                input: $input,
                output: $output,
                question: $question
            );
        }
        $isCreated = false;
        try {
            $this->initService->createConfigurationByScope($scope);
            $isCreated = true;
            $io->info(sprintf('Configuration for scope "%s" was successfuly created.', $scope));
        } catch (ConfigurationAlreadyExistException $exception) {
            $io->info($exception->getMessage());
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());
            return Command::FAILURE;
        }

        if ($scope == Scope::SCOPE_LOCAL) {
            if ($isCreated) {
                $question = 'Want to add global hosts url?';
            } else {
                $question = 'Want to update global hosts url?';
            }
            $updateGlobalUrl = $io->confirm($question, false);

            if ($updateGlobalUrl) {
                $globalUrl = $io->ask(
                    'Please enter global hosts configuration url: ',
                    '',
                    function ($url) {
                        return !filter_var($url, FILTER_VALIDATE_URL) === false;
                    }
                );
                if ($globalUrl === false) {
                    $io->error('invalid url provided');
                    return Command::FAILURE;
                } else {
                    $this->initService->addGlobalHostUrl($globalUrl, !$isCreated);
                    $info = $isCreated ? 'Global Host File Url added.' : 'Global Host File Url updated.';
                    $io->info($info);
                }
            }
        }

        $io->listing(["To add local/project hosts use host:add", "To list existing entries use host:list"]);

        return Command::SUCCESS;
    }
}