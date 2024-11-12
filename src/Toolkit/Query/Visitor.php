<?php

namespace Kirby\Toolkit\Query;

use Closure;
use Exception;
use ReflectionClass;

abstract class Visitor {
	protected Closure|null $interceptor = null;

	function visitNode(AST\Node $node): mixed {
		$shortName = (new ReflectionClass($node))->getShortName();

		// remove the "Node" suffix
		$shortName = substr($shortName, 0, -4);

		$method = 'visit' . $shortName;
		if(method_exists($this, $method)) {
			return $this->$method($node);
		}

		throw new Exception("No visitor method for " . $node::class);
	}

	abstract function visitArgumentList(AST\ArgumentListNode $node): mixed;
	abstract function visitArrayList(AST\ArrayListNode $node): mixed;
	abstract function visitCoalesce(AST\CoalesceNode $node): mixed;
	abstract function visitLiteral(AST\LiteralNode $node): mixed;
	abstract function visitMemberAccess(AST\MemberAccessNode $node): mixed;
	abstract function visitTernary(AST\TernaryNode $node): mixed;
	abstract function visitVariable(AST\VariableNode $node): mixed;
	abstract function visitGlobalFunction(AST\GlobalFunctionNode $node): mixed;
	abstract function visitClosure(AST\ClosureNode $node): mixed;

	/**
	 * Sets and activates an interceptor closure that is called for each resolved value.
	 */
	public function setInterceptor(Closure $interceptor): void {
		$this->interceptor = $interceptor;
	}
}
