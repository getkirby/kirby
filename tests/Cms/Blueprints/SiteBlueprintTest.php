<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteBlueprint::class)]
class SiteBlueprintTest extends TestCase
{
	public function testOptions(): void
	{
		$blueprint = new SiteBlueprint([
			'model' => new Site()
		]);

		$expected = [
			'changeTitle' => null,
			'update'      => null,
		];

		$this->assertSame($expected, $blueprint->options());
	}
}
