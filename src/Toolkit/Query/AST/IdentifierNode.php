<?php

namespace Kirby\Toolkit\Query\AST;

abstract class IdentifierNode extends Node {
	/**
	 * Replaces the escaped identifier with the actual identifier
	 */
	static public function unescape(string $name): string {
		return stripslashes($name);
	}
}
