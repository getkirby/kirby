<?php

namespace Kirby\Template;

use Kirby\Cms\App;

class TestCase extends \Kirby\TestCase
{
	protected function tearDown(): void
	{
		while (Snippet::$current !== null) {
			Snippet::$current->close();
		}

		App::destroy();
	}
}
