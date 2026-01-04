<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

final class GitRepository
{
    private string $originalBranch;

    public function __construct(
        private readonly GitRunner $git
    ) {
        $this->originalBranch = $this->currentBranch();
    }

    public function ensureClean(): void
    {
        $status = $this->git->run('status --porcelain');
        if ($status !== '') {
            throw new RuntimeException(
                'Working tree is not clean. Commit or stash changes before running BreakRadar.'
            );
        }
    }

    public function currentBranch(): string
    {
        return $this->git->run('branch --show-current');
    }

    public function restore(): void
    {
        $this->checkout($this->originalBranch);
    }

    public function checkout(string $ref): void
    {
        $this->git->run("checkout {$ref}");
    }

    public function defaultRemoteBranch(): string
    {
        try {
            $ref = $this->git->run('symbolic-ref refs/remotes/origin/HEAD');
            return str_replace('refs/remotes/', '', $ref);
        } catch (\Throwable) {}

        if ($this->remoteExists('main')) {
            return 'origin/main';
        }

        if ($this->remoteExists('master')) {
            return 'origin/master';
        }

        throw new RuntimeException('Unable to detect default branch.');
    }

    public function fetch(string $ref): void
    {
        $this->git->run('fetch origin');
    }

    private function remoteExists(string $branch): bool
    {
        try {
            $this->git->run("ls-remote --exit-code --heads origin {$branch}");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
