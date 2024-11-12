<?php

namespace Kirby\Toolkit\Query\AST;

use Kirby\Toolkit\Query\Visitor;

class Node {
	public function accept(Visitor $visitor) {
		return $visitor->visitNode($this);
	}
}
