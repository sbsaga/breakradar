<?php

namespace BreakRadar\Analyzer;

use ReflectionClass;
use ReflectionMethod;

class PublicApiAnalyzer
{
    public function analyze(string $srcDir): array
    {
        $result = [];

        foreach ($this->phpFiles($srcDir) as $file) {
            require_once $file;
        }

        foreach (get_declared_classes() as $class) {
            $ref = new ReflectionClass($class);

            if (!$ref->isUserDefined()) {
                continue;
            }

            if (str_starts_with($ref->getName(), 'Symfony\\')) {
                continue;
            }

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isConstructor()) {
                    continue;
                }

                $result[$ref->getName()][] = [
                    'method' => $method->getName(),
                    'params' => array_map(
                        fn($p) => $p->getName(),
                        $method->getParameters()
                    ),
                ];
            }
        }

        ksort($result);

        return $result;
    }

    private function phpFiles(string $dir): array
    {
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir)
        );

        $files = [];

        foreach ($rii as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
