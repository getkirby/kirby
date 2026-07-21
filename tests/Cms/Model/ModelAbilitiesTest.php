<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

class ExtendedModelAbilities extends ModelAbilities
{
	public function delete(): bool
	{
		return false;
	}
}

#[CoversClass(ModelAbilities::class)]
class ModelAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.ModelAbilities';

	public function testHas(): void
	{
		$abilities = new ExtendedModelAbilities();

		$this->assertTrue($abilities->has('delete'));
	}

	public function testHasWithoutCheckMethod(): void
	{
		$abilities = new ExtendedModelAbilities();

		$this->assertFalse($abilities->has('update'));
		$this->assertFalse($abilities->has('somethingCompletelyUnknown'));
	}

	public function testHasWithOwnMethods(): void
	{
		$abilities = new ExtendedModelAbilities();

		$this->assertFalse($abilities->has('has'));
	}
}
