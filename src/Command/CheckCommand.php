<?php

namespace BreakRadar\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force snapshot regeneration'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');

        $git = new GitRepository(new GitRunner());
        $storage = new SnapshotStorage();

        // Clear old snapshots if --force
        if ($force) {
            $output->writeln("<comment>Clearing old snapshots...</comment>");
            $storage->clear();
        }

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

    public function testBreakRadar(): void
{
    echo "This is a test method for BreakRadar detection";
}

    public function doSomething($param) {}


}
