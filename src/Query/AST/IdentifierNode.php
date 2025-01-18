<?php

namespace Kirby\Query\AST;

abstract class IdentifierNode extends Node
{
	/**
	 * Replaces the escaped identifier with the actual identifier
	 */
	public static function unescape(string $name): string
	{
		return stripslashes($name);
	}
}
