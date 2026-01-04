<?php

namespace BreakRadar\Analyzer;

use RuntimeException;

final class SnapshotLoader
{
    public function load(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Snapshot not found: {$path}");
        }

        return json_decode(
            file_get_contents($path),
            true,
            JSON_THROW_ON_ERROR
        );
    }
}
