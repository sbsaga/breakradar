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
use BreakRadar\Analyzer\SnapshotLoader;
use BreakRadar\Diff\BreakingChangeDiff;
use BreakRadar\Reporter\ConsoleReporter;

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
        $loader = new SnapshotLoader();
        $diff = new BreakingChangeDiff();
        $reporter = new ConsoleReporter();

        $baseRef = $git->defaultRemoteBranch();

        try {
            $output->writeln("<info>Fetching base branch: {$baseRef}</info>");
            $git->fetch($baseRef);

            // BASE SNAPSHOT
            $output->writeln("<info>Snapshotting base branch</info>");
            $git->checkout($baseRef);

            $baseApi = $analyzer->analyze(getcwd() . '/src');
            $storage->write('base', $baseApi);

            // HEAD SNAPSHOT
            $output->writeln("<info>Snapshotting current branch</info>");
            $git->restore();

            $headApi = $analyzer->analyze(getcwd() . '/src');
            $storage->write('head', $headApi);

            // DIFF
            $issues = $diff->diff($baseApi, $headApi);

            return $reporter->report($issues, $output);

        } finally {
            $git->restore();
        }
    }

    public function legacy(): void
    {
    }


}
