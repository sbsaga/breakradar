<?php

namespace BreakRadar\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BreakRadar\Analyzer\GitRunner;
use BreakRadar\Analyzer\GitRepository;
use BreakRadar\Analyzer\SnapshotStorage;
use BreakRadar\Analyzer\PublicApiAnalyzer;

#[AsCommand(
    name: 'check',
    description: 'Detect breaking changes between branches'
)]
class CheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $git = new GitRepository(new GitRunner());
        $storage = new SnapshotStorage();
        $analyzer = new PublicApiAnalyzer();

        $current = $git->currentBranch();

        $output->writeln("<info>Snapshotting branch: {$current}</info>");

        $api = $analyzer->analyze(getcwd() . '/src');

        $storage->write('head', $api);

        $output->writeln('<info>Snapshot saved (.breakradar/head.json)</info>');

        return Command::SUCCESS;
    }
}
