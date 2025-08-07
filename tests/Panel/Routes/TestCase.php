<?php

namespace Kirby\Panel\Routes;

use Kirby\Panel\Area;
use Kirby\Panel\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected Area $area;

	public function setUp(): void
	{
		parent::setUp();
		$this->area = new Area(id: 'test');
	}
}
