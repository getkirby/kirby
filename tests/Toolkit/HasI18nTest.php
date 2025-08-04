<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;

class HasI18nTest extends TestCase
{
	protected function object(): object
	{
		return new class () {
			use HasI18n;

			public function translate($key, $data = null)
			{
				return $this->i18n($key, $data);
			}
		};
	}

	public function testI18n(): void
	{
		$class = $this->object();

		$this->assertNull($class->translate(null));
		$this->assertSame('Hello', $class->translate('Hello'));
		$this->assertSame('Hello', $class->translate(fn () => 'Hello'));
		$this->assertSame('Hello', $class->translate(['Hello']));
		$this->assertSame('Hello', $class->translate(['Hello', 'World']));

		$this->assertSame('Copy all', $class->translate('copy.all'));
		$this->assertSame('3 copied!', $class->translate('copy.success.multiple', ['count' => 3]));
	}
}
