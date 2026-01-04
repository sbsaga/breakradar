<?php

namespace BreakRadar\Diff;

class BreakingChangeDiff
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
                $found = false;

                foreach ($head[$class] as $newMethod) {
                    if ($method['method'] === $newMethod['method']) {
                        $found = true;

                        if ($method['params'] !== $newMethod['params']) {
                            $issues[] = sprintf(
                                'Method signature changed: %s::%s(%s → %s)',
                                $class,
                                $method['method'],
                                implode(', ', $method['params']),
                                implode(', ', $newMethod['params'])
                            );
                        }

                        break;
                    }
                }

                if (!$found) {
                    $issues[] = "Public method removed: {$class}::{$method['method']}";
                }
            }
        }

        return $issues;
    }
}
