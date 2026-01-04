<?php

namespace BreakRadar\Diff;

final class BreakingChangeDiff
{
    public function diff(array $base, array $head): array
    {
        $issues = [];

        foreach ($base as $class => $methods) {
            if (!isset($head[$class])) {
                $issues[] = "Public class removed: {$class}";
                continue;
            }

            foreach ($methods as $method) {
                if (!in_array($method, $head[$class], true)) {
                    $issues[] = "Public method removed: {$class}::{$method}";
                }
            }
        }

        return $issues;
    }
}
