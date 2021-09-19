<?php

namespace Kirby\Sane;

use FilesystemIterator;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TestCase extends BaseTestCase
{
    protected $type;

    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/tmp');
    }

    /**
     * Returns the path to a test fixture file
     *
     * @param string $name Fixture name including file extension
     * @param bool $tmp If true, the fixture will be copied to a temporary location
     * @return string
     */
    protected function fixture(string $name, bool $tmp = false): string
    {
        $fixtureRoot = __DIR__ . '/fixtures/' . $this->type . '/' . $name;

        if ($tmp === false) {
            return $fixtureRoot;
        }

        $tmpRoot = __DIR__ . '/tmp/' . $this->type . '/' . $name;
        F::copy($fixtureRoot, $tmpRoot);
        return $tmpRoot;
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
