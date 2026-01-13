<?php

namespace Kirby;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Locale;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected App $app;

	protected string|Closure|null $i18nLocale;
	protected string|array|Closure|null $i18nFallback;
	protected Closure|null $i18nLoad;
	protected array $i18nTranslations;
	protected array|string $localeBackup;

	public function setUp(): void
	{
		App::destroy();

		$translationRoot = dirname(__DIR__) . '/i18n/translations';

		$this->localeBackup = Locale::get();
		Locale::set('C');

		$this->i18nLocale = I18n::$locale;
		$this->i18nFallback = I18n::$fallback;
		$this->i18nLoad = I18n::$load;
		$this->i18nTranslations = I18n::$translations;

		I18n::$locale = 'en';
		I18n::$fallback = 'en';
		I18n::$load = function (string $locale) use ($translationRoot): array {
			$file = $translationRoot . '/' . basename($locale) . '.json';
			return Data::read($file, fail: false);
		};
		I18n::$translations = [];
	}

	public function tearDown(): void
	{
		if (isset($this->localeBackup) === true) {
			Locale::set($this->localeBackup);
		}

		if (isset($this->i18nLocale) === true) {
			I18n::$locale = $this->i18nLocale;
		}

		if (isset($this->i18nFallback) === true) {
			I18n::$fallback = $this->i18nFallback;
		}

		if (isset($this->i18nLoad) === true) {
			I18n::$load = $this->i18nLoad;
		}

		if (isset($this->i18nTranslations) === true) {
			I18n::$translations = $this->i18nTranslations;
		}
	}

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
