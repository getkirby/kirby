<?php

namespace Kirby\Panel\Routes;

use Kirby\Panel\Area;
use Kirby\Panel\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected Area $area;

	protected function setUp(): void
	{
		parent::setUp();
		$this->area = new Area(id: 'test');
	}
}
