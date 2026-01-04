<?php

namespace BreakRadar\Analyzer;

class GitRepository
{
    public function __construct(
        private GitRunner $git
    ) {}

    public function currentBranch(): string
    {
        return $this->git->run('rev-parse --abbrev-ref HEAD');
    }

    public function checkout(string $ref): void
    {
        $this->git->run("checkout {$ref}");
    }
}
