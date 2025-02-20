<?php

namespace Kirby\Cms;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\SiteBlueprint
 */
class SiteBlueprintTest extends TestCase
{
	public function testOptions()
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
