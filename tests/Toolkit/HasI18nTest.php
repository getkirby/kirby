<?php

namespace Kirby\Toolkit;

use Closure;
use Kirby\TestCase;

class HasI18nTest extends TestCase
{
	protected string|Closure $localeBackup;

	public function setUp(): void
	{
		$this->localeBackup = I18n::$locale;
		I18n::$locale = 'foo';
		I18n::$translations['foo'] = [
			'my.key'   => 'My translation',
			'my.count' => 'All {count} things'
		];
	}

	public function tearDown(): void
	{
		unset(I18n::$translations['foo']);
		I18n::$locale = $this->localeBackup;
	}

	public function testI18n(): void
	{
		$class = new class () {
			use HasI18n;

			public function translate($key, $data = null)
			{
				return $this->i18n($key, $data);
			}
		};

		$this->assertNull($class->translate(null));
		$this->assertSame('Hello', $class->translate('Hello'));
		$this->assertSame('Hello', $class->translate('Hello', ['data' => 'data']));
		$this->assertSame('Hello', $class->translate(fn () => 'Hello'));
		$this->assertSame('Hello', $class->translate(['Hello']));
		$this->assertSame('Hello', $class->translate(['Hello', 'World']));

		$this->assertSame('My translation', $class->translate('my.key'));
		$this->assertSame('All 3 things', $class->translate('my.count', ['count' => 3]));
	}
}
