<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public function tearDown(): void
	{
		while (Snippet::$current !== null) {
			Snippet::$current->close();
		}

		App::destroy();
	}
}
