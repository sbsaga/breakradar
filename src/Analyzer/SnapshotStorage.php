<?php

namespace BreakRadar\Analyzer;

final class SnapshotStorage
{
    private string $dir;

    public function __construct()
    {
        $this->dir = sys_get_temp_dir() . '/breakradar';

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    public function path(string $name): string
    {
        return "{$this->dir}/{$name}.json";
    }

    public function write(string $name, array $data): void
    {
        file_put_contents(
            $this->path($name),
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
