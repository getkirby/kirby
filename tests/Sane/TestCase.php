<?php

namespace Kirby\Sane;

use FilesystemIterator;
use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase as BaseTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class TestCase extends BaseTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	protected static string $type;

	public function setUp(): void
	{
		new App([
			'urls' => [
				'index' => 'https://getkirby.com/subfolder'
			]
		]);
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}

	/**
	 * Returns the path to a test fixture file
	 *
	 * @param string $name Fixture name including file extension
	 * @param bool $tmp If true, the fixture will be copied to a temporary location
	 */
	protected function fixture(string $name, bool $tmp = false): string
	{
		$fixtureRoot = static::FIXTURES . '/' . static::$type . '/' . $name;

		if ($tmp === false) {
			return $fixtureRoot;
		}

		$tmpRoot = static::TMP . '/' . static::$type . '/' . $name;
		F::copy($fixtureRoot, $tmpRoot);
		return $tmpRoot;
	}

	/**
	 * Returns a list of all fixture files in the given fixture
	 * directory; works recursively
	 *
	 * @param string $directory `'allowed'`, `'disallowed'` or `'invalid'`
	 * @param string $extension File extension to filter by
	 */
	protected static function fixtureList(
		string $directory,
		string $extension
	): array {
		$root = static::FIXTURES . '/' . static::$type;

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
