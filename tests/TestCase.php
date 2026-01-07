<?php

namespace Kirby;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected App $app;

	/**
	 * Whether $actual is a File object
	 * and optionally if it matches $expected (by reference or ID)
	 */
	public function assertIsFile($expected, $actual = null): void
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

	/**
	 * Whether $actual is a Page object
	 * and optionally if it matches $expected (by reference or ID)
	 */
	public function assertIsPage($expected, $actual = null): void
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

	/**
	 * Whether $actual is a Site object
	 */
	public function assertIsSite($expected, $actual = null): void
	{
		$this->assertInstanceOf(Site::class, $actual ?? $expected);

		if ($actual !== null) {
			$this->assertSame($expected, $actual);
		}
	}

	/**
	 * Whether $actual is a User object
	 * and optionally if it matches $expected (by reference or ID)
	 */
	public function assertIsUser($expected, $actual = null): void
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

	/**
	 * Checks if the test class extending this test case class
	 * has defined a temporary directory
	 */
	protected function hasTmp(): bool
	{
		return defined(static::class . '::TMP');
	}

	/**
	 * Set up a new multi language app instance with
	 * English and German pre-installed
	 */
	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		$this->app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => $site ?? []
		]);
	}

	/**
	 * Set up a new single language app instance
	 */
	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => $site ?? []
		]);
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
