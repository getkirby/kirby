<?php

namespace Kirby\Toolkit\Query;

use Closure;
use Exception;
use ReflectionClass;

abstract class Visitor
{
	protected Closure|null $interceptor = null;

	public function visitNode(AST\Node $node): mixed
	{
		$shortName = (new ReflectionClass($node))->getShortName();

		// remove the "Node" suffix
		$shortName = substr($shortName, 0, -4);

		$method = 'visit' . $shortName;
		if(method_exists($this, $method)) {
			return $this->$method($node);
		}

		throw new Exception('No visitor method for ' . $node::class);
	}

	abstract public function visitArgumentList(AST\ArgumentListNode $node): mixed;
	abstract public function visitArrayList(AST\ArrayListNode $node): mixed;
	abstract public function visitCoalesce(AST\CoalesceNode $node): mixed;
	abstract public function visitLiteral(AST\LiteralNode $node): mixed;
	abstract public function visitMemberAccess(AST\MemberAccessNode $node): mixed;
	abstract public function visitTernary(AST\TernaryNode $node): mixed;
	abstract public function visitVariable(AST\VariableNode $node): mixed;
	abstract public function visitGlobalFunction(AST\GlobalFunctionNode $node): mixed;
	abstract public function visitClosure(AST\ClosureNode $node): mixed;

	/**
	 * Sets and activates an interceptor closure that is called for each resolved value.
	 */
	public function setInterceptor(Closure $interceptor): void
	{
		$this->interceptor = $interceptor;
	}
}
