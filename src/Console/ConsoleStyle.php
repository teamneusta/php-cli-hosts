<?php

namespace TeamNeusta\Hosts\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleStyle extends SymfonyStyle
{
    public function __construct(
        InputInterface $input,
        private OutputInterface $output,
    ) {
        parent::__construct($input, $output);
    }

    public function success($message): void
    {
        $this->writeln('<fg=green;options=bold,underscore>OK</> '.$message);
    }

    public function comment($message): void
    {
        $this->text($message);
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
