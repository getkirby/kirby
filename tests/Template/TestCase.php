<?php

namespace Kirby\Template;

class TestCase extends \Kirby\TestCase
{
	protected function tearDown(): void
	{
		while (Snippet::$current !== null) {
			Snippet::$current->close();
		}
	}
}
