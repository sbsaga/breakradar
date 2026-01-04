<?php

namespace BreakRadar\Analyzer;

final class PublicApiAnalyzer
{
    public function analyze(string $srcDir, string $namespacePrefix): array
    {
        $api = [];

        foreach ($this->phpFiles($srcDir) as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            $tokens = token_get_all($code);
            $this->extract($tokens, $api, $namespacePrefix);
        }

        ksort($api);
        return $api;
    }

    private function extract(array $tokens, array &$api, string $nsPrefix): void
    {
        $namespace = '';
        $class = null;
        $visibility = 'public';

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; isset($tokens[$j]); $j++) {
                    if ($tokens[$j] === ';') {
                        break;
                    }
                    if (is_array($tokens[$j])) {
                        $namespace .= $tokens[$j][1];
                    }
                }
                $namespace = trim($namespace);
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $class = $tokens[$i + 2][1] ?? null;
                if (!$class) {
                    continue;
                }

                $fqcn = $namespace . '\\' . $class;

                if (!str_starts_with($fqcn, $nsPrefix)) {
                    $class = null;
                } else {
                    $api[$fqcn] = [];
                }
            }

            if (is_array($token) && $token[0] === T_PUBLIC) {
                $visibility = 'public';
            }

            if (
                is_array($token)
                && $token[0] === T_FUNCTION
                && $class
                && $visibility === 'public'
            ) {
                $method = $tokens[$i + 2][1] ?? null;
                if ($method) {
                    $api[$namespace . '\\' . $class][] = $method;
                }
            }
        }
    }

    private function phpFiles(string $dir): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }
}
