<?php

namespace BreakRadar\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleReporter
{
    public function report(array $issues, OutputInterface $output): int
    {
        if (empty($issues)) {
            $output->writeln('<info>No breaking changes detected.</info>');
            return 0;
        }

        $output->writeln('<error>Breaking changes detected:</error>');
        foreach ($issues as $issue) {
            $output->writeln(" - {$issue}");
        }

        return 1;
    }
}
