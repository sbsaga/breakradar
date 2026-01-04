<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

class GitRepository
{
    private string $originalBranch;

    public function __construct(
        private GitRunner $git
    ) {
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

    public function restore(): void
    {
        $this->checkout($this->originalBranch);
    }

    public function checkout(string $ref): void
    {
        $this->git->run("checkout {$ref}");
    }

    /**
     * Production-safe default branch detection
     */
    public function defaultRemoteBranch(): string
    {
        // 1️⃣ Try origin/HEAD
        try {
            $ref = $this->git->run('symbolic-ref refs/remotes/origin/HEAD');
            return str_replace('refs/remotes/', '', $ref);
        } catch (\Throwable) {
            // continue
        }

        // 2️⃣ Fallback to main
        if ($this->remoteBranchExists('main')) {
            return 'origin/main';
        }

        // 3️⃣ Fallback to master
        if ($this->remoteBranchExists('master')) {
            return 'origin/master';
        }

        throw new RuntimeException(
            'Unable to detect default branch. Expected origin/main or origin/master.'
        );
    }

    public function fetch(string $ref): void
    {
        try {
            $this->git->run("fetch origin {$ref}");
        } catch (\Throwable) {
            // Safe fallback
            $this->git->run("fetch origin");
        }
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
