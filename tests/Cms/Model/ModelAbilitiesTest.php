<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

class ExtendedModelAbilities extends ModelAbilities
{
}

#[CoversClass(ModelAbilities::class)]
class ModelAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.ModelAbilities';

	public function testCallWithoutArguments(): void
	{
		$abilities = new ExtendedModelAbilities();

		$this->assertTrue($abilities->delete());
		$this->assertTrue($abilities->update());
		$this->assertTrue($abilities->somethingCompletelyUnknown());
	}

	public function testCallWithArguments(): void
	{
		$abilities = new ExtendedModelAbilities();

		$this->assertTrue($abilities->changeTemplate('article'));
	}
}
