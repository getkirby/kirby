<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteBlueprint::class)]
class SiteBlueprintTest extends TestCase
{
	public function testOptions()
	{
		$blueprint = new SiteBlueprint([
			'model' => new Site()
		]);

		$expected = [
			'access'      => null,
			'changeTitle' => null,
			'update'      => null,
		];

		$this->assertSame($expected, $blueprint->options());
	}
}
