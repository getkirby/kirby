<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;

class HasI18nTest extends TestCase
{
	public function testI18n(): void
	{
		$class = new class () {
			use HasI18n;

			public function translate($key, $data = null)
			{
				return $this->i18n($key, $data);
			}
		};

		// set up custom i18n strings for test
		I18n::$translations['en'] = [
			'my.key'   => 'My translation',
			'my.count' => 'All {count} things'
		];

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
