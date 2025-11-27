<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BaseField::class)]
class BaseFieldTest extends TestCase
{
	public function testStringTemplateWithEmptyValue(): void
	{
		$testClass = new class () extends BaseField {
			public function stringTemplateTest($value)
			{
				return $this->stringTemplate($value);
			}
		};

		$this->assertNull($testClass->stringTemplateTest(null));
		$this->assertSame('', $testClass->stringTemplateTest(''));
	}

	public function testStringTemplateI18nWithEmptyValue(): void
	{
		$testClass = new class () extends BaseField {
			public function stringTemplateI18nTest($value)
			{
				return $this->stringTemplateI18n($value);
			}
		};

		$this->assertNull($testClass->stringTemplateI18nTest(null));
		$this->assertSame('', $testClass->stringTemplateI18nTest(''));
	}
}
