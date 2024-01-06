<?php

namespace Kirby;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public function assertIsFile(string|File $expected, $actual = null): void
	{
		$this->assertInstanceOf(File::class, $actual ?? $expected);

		if ($actual !== null) {
			if (is_string($expected) === true) {
				$this->assertSame($expected, $actual->id());
			}

			if ($expected instanceof File) {
				$this->assertSame($expected, $actual);
			}
		}
	}

	public function assertIsPage(string|Page $expected, $actual = null): void
	{
		$this->assertInstanceOf(Page::class, $actual ?? $expected);

		if ($actual !== null) {
			if (is_string($expected) === true) {
				$this->assertSame($expected, $actual->id());
			}

			if ($expected instanceof Page) {
				$this->assertSame($expected, $actual);
			}
		}
	}

	public function assertIsUser(string|User $expected, $actual = null): void
	{
		$this->assertInstanceOf(User::class, $actual ?? $expected);

		if ($actual !== null) {
			if (is_string($expected) === true) {
				$this->assertSame($expected, $actual->id());
			}

			if ($expected instanceof User) {
				$this->assertSame($expected, $actual);
			}
		}
	}

	public function assertIsSite($actual): void
	{
		$this->assertInstanceOf(Site::class, $actual);
	}

	/**
	 * Checks if the test class extending this test case class
	 * has defined a temporary directory
	 */
	protected function hasTmp(): bool
	{
		return defined(static::class . '::TMP');
	}

	protected function setUpTmp(): void
	{
		if ($this->hasTmp() === true) {
			Dir::make(static::TMP);
		}
	}

	protected function tearDownTmp(): void
	{
		if ($this->hasTmp() === true) {
			Dir::remove(static::TMP);
		}
	}
}
