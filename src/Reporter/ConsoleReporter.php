<?php

namespace BreakRadar\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleReporter
{
    public function __construct(
        private OutputInterface $output
    ) {}

    public function info(string $message): void
    {
        $this->output->writeln($message, OutputInterface::VERBOSITY_VERBOSE);
    }

    public function debug(string $message): void
    {
        $this->output->writeln($message, OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    public function trace(string $message): void
    {
        $this->output->writeln($message, OutputInterface::VERBOSITY_DEBUG);
    }

    public function report(array $breakingChanges): int
    {
        if (empty($breakingChanges)) {
            $this->output->writeln(
                '<info>No breaking changes detected.</info>'
            );
            return 0;
        }

        $this->output->writeln(
            '<error>Breaking changes detected:</error>'
        );

        foreach ($breakingChanges as $change) {
            $this->output->writeln(" - {$change}");
        }

        return 1;
    }
}
