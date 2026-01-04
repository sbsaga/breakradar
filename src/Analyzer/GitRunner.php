<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

class GitRunner
{
    public function run(string $command): string
    {
        $full = 'git ' . $command . ' 2>&1';
        exec($full, $output, $code);

        if ($code !== 0) {
            throw new RuntimeException(
                "Git command failed: {$command}\n" . implode("\n", $output)
            );
        }

        return trim(implode("\n", $output));
    }
}
