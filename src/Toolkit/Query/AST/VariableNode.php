<?php

namespace Kirby\Toolkit\Query\AST;

class VariableNode extends IdentifierNode {
	public function __construct(
		public string $name,
	) {}

	/**
	 * Replaces escaped dots with real dots
	 */
	public function name(): string {
		return self::unescape($this->name);
	}
}
