<?php

namespace Kirby\Data;

class CustomHandler extends Json
{
}

/**
 * Encoding can fail for data a handler cannot represent.
 * The file must stay untouched when that happens.
 */
class BrokenHandler extends Json
{
	public static function encode($data, bool $pretty = false): string
	{
		throw new \Exception('Encoding failed');
	}
}

class CustomInvalidHandler
{
}
