<?php

namespace Kirby\Template;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected function tearDown(): void
	{
		while (Snippet::$current !== null) {
			Snippet::$current->close();
		}
	}
}
