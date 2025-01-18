<?php

namespace Kirby\Query\Runners\Visitors;

use Closure;
use Exception;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\ArrayListNode;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\GlobalFunctionNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\TernaryNode;
use Kirby\Query\AST\VariableNode;
use Kirby\Query\Runtime;
use Kirby\Query\Visitor;

/**
 * Visitor that interprets and directly executes a query AST.
 */
class Interpreter extends Visitor
{
	/**
	 * @param array<string,Closure> $validGlobalFunctions An array of valid global function closures.
	 * @param array<string,mixed> $context The data bindings for the query.
	 */
	public function __construct(
		public array $validGlobalFunctions = [],
		public array $context = []
	) {
	}

	public function visitArgumentList(ArgumentListNode $node): array
	{
		return array_map(
			fn ($argument) => $argument->accept($this),
			$node->arguments
		);
	}

	public function visitArrayList(ArrayListNode $node): mixed
	{
		return array_map(
			fn ($element) => $element->accept($this),
			$node->elements
		);
	}

	public function visitCoalesce(CoalesceNode $node): mixed
	{
		return $node->left->accept($this) ?? $node->right->accept($this);
	}

	public function visitLiteral(LiteralNode $node): mixed
	{
		$val = $node->value;

		return $val;
	}

	public function visitMemberAccess(MemberAccessNode $node): mixed
	{
		$left = $node->object->accept($this);

		if ($this->interceptor !== null) {
			$left = ($this->interceptor)($left);
		}

		$item = null;

		if ($node->arguments !== null) {
			$item = Runtime::access(
				$left,
				$node->member,
				$node->nullSafe,
				...$node->arguments->accept($this)
			);
		} else {
			$item = Runtime::access($left, $node->member, $node->nullSafe);
		}

		return $item;
	}

	public function visitTernary(TernaryNode $node): mixed
	{
		if ($node->trueBranchIsDefault === true) {
			return
				$node->condition->accept($this) ?:
				$node->trueBranch->accept($this);
		}

		return
			$node->condition->accept($this) ?
			$node->trueBranch->accept($this) :
			$node->falseBranch->accept($this);
	}

	public function visitVariable(VariableNode $node): mixed
	{
		// what looks like a variable might actually be a global function
		// but if there is a variable with the same name, the variable takes precedence

		$name = $node->name();

		$item = match (true) {
			isset($this->context[$name]) => $this->context[$name] instanceof Closure ? $this->context[$name]() : $this->context[$name],
			isset($this->validGlobalFunctions[$name]) => $this->validGlobalFunctions[$name](),
			default => null,
		};

		return $item;
	}

	public function visitGlobalFunction(GlobalFunctionNode $node): mixed
	{
		$name = $node->name();

		if (isset($this->validGlobalFunctions[$name]) === false) {
			throw new Exception("Invalid global function $name");
		}

		$function = $this->validGlobalFunctions[$name];

		$result = $function(...$node->arguments->accept($this));

		return $result;
	}

	public function visitClosure(ClosureNode $node): mixed
	{
		$self = $this;

		return function (...$params) use ($self, $node) {
			$context   = $self->context;
			$functions = $self->validGlobalFunctions;

			// [key1, key2] + [value1, value2] => [key1 => value1, key2 => value2]
			$arguments = array_combine(
				$node->arguments,
				$params
			);

			$visitor = new static($functions, [...$context, ...$arguments]);

			if ($self->interceptor !== null) {
				$visitor->setInterceptor($self->interceptor);
			}

			return $node->body->accept($visitor);
		};
	}
}
