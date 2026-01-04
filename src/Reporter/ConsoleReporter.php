<?php

namespace BreakRadar\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleReporter
{
    public function __construct(private OutputInterface $output) {}

    public function info(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    public function debug(string $message): void
    {
        $this->output->writeln("<comment>{$message}</comment>");
    }

    public function report(array $issues): int
    {
        if (empty($issues)) {
            $this->output->writeln('<info>No breaking changes detected.</info>');
            return 0;
        }

        $this->output->writeln('<error>Breaking changes detected:</error>');
        foreach ($issues as $issue) {
            $this->output->writeln(" - {$issue}");
        }

        return 1;
    }
}
