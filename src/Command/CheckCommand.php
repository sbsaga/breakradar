<?php

namespace BreakRadar\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BreakRadar\Analyzer\{GitRunner, GitRepository, PublicApiAnalyzer, SnapshotStorage};
use BreakRadar\Diff\BreakingChangeDiff;
use BreakRadar\Reporter\ConsoleReporter;

#[AsCommand(name: 'check', description: 'Detect breaking changes')]
final class CheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reporter = new ConsoleReporter($output);
        $git      = new GitRepository(new GitRunner());
        $git->ensureClean();

        $storage  = new SnapshotStorage();
        $analyzer = new PublicApiAnalyzer();
        $diff     = new BreakingChangeDiff();

        $baseRef = $git->defaultRemoteBranch();

        try {
            $reporter->info("Fetching base branch: {$baseRef}");
            $git->fetch($baseRef);

            $reporter->debug("Snapshotting base branch");
            $git->checkout($baseRef);
            $base = $analyzer->analyze(getcwd() . '/src');
            $storage->write('base', $base);

            $reporter->debug("Snapshotting current branch");
            $git->restore();
            $head = $analyzer->analyze(getcwd() . '/src');
            $storage->write('head', $head);

            return $reporter->report($diff->diff($base, $head));
        } finally {
            $git->restore();
        }
    }
}