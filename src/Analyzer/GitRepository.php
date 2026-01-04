<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

class GitRepository
{
    private string $originalBranch;

    public function __construct(
        private GitRunner $git
    ) {
        $this->originalBranch = $this->currentBranchInternal();
    }

    private function currentBranchInternal(): string
    {
        return $this->git->run('branch --show-current');
    }

    public function currentBranch(): string
    {
        return $this->originalBranch;
    }

    public function checkout(string $ref): void
    {
        $this->git->run("checkout {$ref}");
    }

    public function restore(): void
    {
        $this->checkout($this->originalBranch);
    }

    public function ensureBaseAvailable(string $base): void
    {
        $this->git->run("fetch origin {$base}");
    }
}
