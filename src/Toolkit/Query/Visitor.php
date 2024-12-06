<?php

namespace Kirby\Toolkit\Query;

use Closure;
use Exception;
use Kirby\Toolkit\Query\AST\ArgumentListNode;
use Kirby\Toolkit\Query\AST\ArrayListNode;
use Kirby\Toolkit\Query\AST\ClosureNode;
use Kirby\Toolkit\Query\AST\CoalesceNode;
use Kirby\Toolkit\Query\AST\GlobalFunctionNode;
use Kirby\Toolkit\Query\AST\LiteralNode;
use Kirby\Toolkit\Query\AST\MemberAccessNode;
use Kirby\Toolkit\Query\AST\Node;
use Kirby\Toolkit\Query\AST\TernaryNode;
use Kirby\Toolkit\Query\AST\VariableNode;
use ReflectionClass;

abstract class Visitor
{
	protected Closure|null $interceptor = null;

	public function visitNode(Node $node): mixed
	{
		$shortName = (new ReflectionClass($node))->getShortName();

		// remove the "Node" suffix
		$shortName = substr($shortName, 0, -4);
		$method    = 'visit' . $shortName;

		if (method_exists($this, $method)) {
			return $this->$method($node);
		}

		throw new Exception('No visitor method for ' . $node::class);
	}

	abstract public function visitArgumentList(ArgumentListNode $node): mixed;
	abstract public function visitArrayList(ArrayListNode $node): mixed;
	abstract public function visitCoalesce(CoalesceNode $node): mixed;
	abstract public function visitLiteral(LiteralNode $node): mixed;
	abstract public function visitMemberAccess(MemberAccessNode $node): mixed;
	abstract public function visitTernary(TernaryNode $node): mixed;
	abstract public function visitVariable(VariableNode $node): mixed;
	abstract public function visitGlobalFunction(GlobalFunctionNode $node): mixed;
	abstract public function visitClosure(ClosureNode $node): mixed;

	/**
	 * Sets and activates an interceptor closure that is called for each resolved value.
	 */
	public function setInterceptor(Closure $interceptor): void
	{
		$this->interceptor = $interceptor;
	}
}
