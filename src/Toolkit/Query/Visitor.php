<?php

namespace Kirby\Toolkit\Query;

use Exception;
use ReflectionClass;

abstract class Visitor {
	function visitNode(AST\Node $node): mixed {
		$shortName = (new ReflectionClass($node))->getShortName();

		$method = 'visit' . $shortName;
		if(method_exists($this, $method)) {
			return $this->$method($node);
		}

		throw new Exception("No visitor method for " . $node::class);
	}

	abstract function visitArgumentList(AST\ArgumentList $node): mixed;
	abstract function visitArrayList(AST\ArrayList $node): mixed;
	abstract function visitCoalesce(AST\Coalesce $node): mixed;
	abstract function visitLiteral(AST\Literal $node): mixed;
	abstract function visitMemberAccess(AST\MemberAccess $node): mixed;
	abstract function visitTernary(AST\Ternary $node): mixed;
	abstract function visitVariable(AST\Variable $node): mixed;
	abstract function visitGlobalFunction(AST\GlobalFunction $node): mixed;
	abstract function visitClosure(AST\Closure $node): mixed;
}
