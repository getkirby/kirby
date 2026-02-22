<?php

namespace Kirby\Auth {
	use Kirby\Tests\MockTime;

	function time(): int
	{
		return \Kirby\Tests\time(MockTime::$time);
	}

	function usleep(...$args): void
	{
		\Kirby\Tests\usleep(...$args);
	}
}
