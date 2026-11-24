<?php

namespace Kirby\Toolkit;

use Kirby\Tests\MockTime;

function time(): int
{
	return \Kirby\Tests\time(MockTime::$time);
}
