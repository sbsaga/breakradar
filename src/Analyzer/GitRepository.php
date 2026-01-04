<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

final class GitRepository
{
    private string $originalBranch;

    public function __construct(private GitRunner $git)
    {
        $this->originalBranch = $this->detectCurrentBranch();
    }

    private function detectCurrentBranch(): string
    {
        return $this->git->run('branch --show-current');
    }

    public function currentBranch(): string
    {
        return $this->originalBranch;
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

    public function restore(): void
    {
        $this->checkout($this->originalBranch);
    }

    public function checkout(string $ref): void
    {
        $this->git->run("checkout {$ref}");
    }

    public function fetch(string $ref): void
    {
        try {
            $this->git->run("fetch origin {$ref}");
        } catch (\Throwable) {
            $this->git->run("fetch origin");
        }
    }

    public function defaultRemoteBranch(): string
    {
        try {
            $ref = $this->git->run('symbolic-ref refs/remotes/origin/HEAD');
            return str_replace('refs/remotes/', '', $ref);
        } catch (\Throwable) {
            // fallback
        }

        foreach (['main', 'master'] as $branch) {
            if ($this->remoteBranchExists($branch)) {
                return "origin/{$branch}";
            }
        }

        throw new RuntimeException(
            'Unable to detect default branch. Expected origin/main or origin/master.'
        );
    }

    private function remoteBranchExists(string $branch): bool
    {
        try {
            $this->git->run("ls-remote --exit-code --heads origin {$branch}");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
