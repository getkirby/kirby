<?php

namespace Kirby\Toolkit\Query\Runners\Visitors;

use Closure;
use Exception;
use Kirby\Toolkit\Query\AST\ArgumentListNode;
use Kirby\Toolkit\Query\AST\ArrayListNode;
use Kirby\Toolkit\Query\AST\ClosureNode;
use Kirby\Toolkit\Query\AST\CoalesceNode;
use Kirby\Toolkit\Query\AST\LiteralNode;
use Kirby\Toolkit\Query\AST\MemberAccessNode;
use Kirby\Toolkit\Query\AST\TernaryNode;
use Kirby\Toolkit\Query\AST\VariableNode;
use Kirby\Toolkit\Query\AST\GlobalFunctionNode;
use Kirby\Toolkit\Query\Runtime;
use Kirby\Toolkit\Query\Visitor;


/**
 * Visitor that interprets and directly executes a query AST.
 */
class Interpreter extends Visitor {
	/**
	 * @param array<string,Closure> $validGlobalFunctions An array of valid global function closures.
	 * @param array<string,mixed> $context The data bindings for the query.
	 */
	public function __construct(
		public array $validGlobalFunctions = [],
		public array $context = []
	) {}

	public function visitArgumentList(ArgumentListNode $node): array {
		return array_map(fn($argument) => $argument->accept($this), $node->arguments);
	}

	public function visitArrayList(ArrayListNode $node): mixed {
		return array_map(fn($element) => $element->accept($this), $node->elements);
	}

	public function visitCoalesce(CoalesceNode $node): mixed {
		return $node->left->accept($this) ?? $node->right->accept($this);
	}

	public function visitLiteral(LiteralNode $node): mixed {
		$val = $node->value;

		if($this->interceptor !== null) {
			$val = ($this->interceptor)($val);
		}

		return $val;
	}

	public function visitMemberAccess(MemberAccessNode $node): mixed {
		$left = $node->object->accept($this);

		$item = null;
		if($node->arguments !== null) {
			$item = Runtime::access($left, $node->member, $node->nullSafe, ...$node->arguments->accept($this));
		} else {
			$item = Runtime::access($left, $node->member, $node->nullSafe);
		}

		if($this->interceptor !== null) {
			$item = ($this->interceptor)($item);
		}

		return $item;
	}

	public function visitTernary(TernaryNode $node): mixed {
		if($node->trueBranchIsDefault) {
			return $node->condition->accept($this) ?: $node->trueBranch->accept($this);
		} else {
			return $node->condition->accept($this) ? $node->trueBranch->accept($this) : $node->falseBranch->accept($this);
		}
	}

	public function visitVariable(VariableNode $node): mixed {
		// what looks like a variable might actually be a global function
		// but if there is a variable with the same name, the variable takes precedence

		$name = $node->name();

		$item = match (true) {
			isset($this->context[$name]) => $this->context[$name] instanceof Closure ? $this->context[$name]() : $this->context[$name],
			isset($this->validGlobalFunctions[$name]) => $this->validGlobalFunctions[$name](),
			default => null,
		};

		if($this->interceptor !== null) {
			$item = ($this->interceptor)($item);
		}

		return $item;
	}

	public function visitGlobalFunction(GlobalFunctionNode $node): mixed {
		$name = $node->name();

		if(!isset($this->validGlobalFunctions[$name])) {
			throw new Exception("Invalid global function $name");
		}

		$function = $this->validGlobalFunctions[$name];
		if($this->interceptor !== null) {
			$function = ($this->interceptor)($function);
		}

		$result = $function(...$node->arguments->accept($this));

		if($this->interceptor !== null) {
			$result = ($this->interceptor)($result);
		}

		return $result;
	}

	public function visitClosure(ClosureNode $node): mixed {
		$self = $this;

		return function(...$params) use ($self, $node) {
			$context = $self->context;
			$functions = $self->validGlobalFunctions;

			// [key1, key2] + [value1, value2] => [key1 => value1, key2 => value2]
			$arguments = array_combine(
				$node->arguments,
				$params
			);

			$visitor = new self($functions, [...$context, ...$arguments]);
			if($self->interceptor !== null) {
				$visitor->setInterceptor($self->interceptor);
			}

			return $node->body->accept($visitor);
		};
	}
}
