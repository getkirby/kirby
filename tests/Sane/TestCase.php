<?php

namespace Kirby\Sane;

use FilesystemIterator;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TestCase extends BaseTestCase
{
    protected $type = 'svg';

    /**
     * Returns the path to a test fixture file
     *
     * @param string $name Fixture name including file extension
     * @return string
     */
    protected function fixture(string $name): string
    {
        return __DIR__ . '/fixtures/' . $this->type . '/' . $name;
    }

    /**
     * Returns a list of all fixture files in the given fixture
     * directory; works recursively
     *
     * @param string $directory `'allowed'`, `'disallowed'` or `'invalid'`
     * @param string $extension File extension to filter by
     * @return array
     */
    protected function fixtureList(string $directory, string $extension): array
    {
        $root = __DIR__ . '/fixtures/' . $this->type;

        $directory = new RecursiveDirectoryIterator(
            $root . '/' . $directory,
            FilesystemIterator::SKIP_DOTS
        );

        $results = [];
        foreach (new RecursiveIteratorIterator($directory) as $file) {
            if ($file->getExtension() !== $extension) {
                continue;
            }

            $results[] = [str_replace($root, '', $file->getPathname())];
        }

        return $results;
    }
}
