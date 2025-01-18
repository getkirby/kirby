<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitor;

class Node
{
	public function accept(Visitor $visitor)
	{
		return $visitor->visitNode($this);
	}
}
