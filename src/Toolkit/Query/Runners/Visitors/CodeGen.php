<?php

namespace Kirby\Toolkit\Query\Runners\Visitors;

use Exception;
use Kirby\Toolkit\Query\AST\ArgumentListNode;
use Kirby\Toolkit\Query\AST\ArrayListNode;
use Kirby\Toolkit\Query\AST\ClosureNode;
use Kirby\Toolkit\Query\AST\CoalesceNode;
use Kirby\Toolkit\Query\AST\GlobalFunctionNode;
use Kirby\Toolkit\Query\AST\LiteralNode;
use Kirby\Toolkit\Query\AST\MemberAccessNode;
use Kirby\Toolkit\Query\AST\TernaryNode;
use Kirby\Toolkit\Query\AST\VariableNode;
use Kirby\Toolkit\Query\Visitor;


/**
 * Visitor that generates code representations from query structures.
 *
 * The `CodeGen` class traverses query nodes and generates corresponding PHP code.
 * It extends the base `Visitor` class, providing implementations specific to code generation.
 */
class CodeGen extends Visitor {

	/**
	 * If we need something from a namespace, we'll add the namespace here into the array key
	 * @var array{string:true}
	 */
	public array $uses = [];

	/**
	 * @var array{string:string}
	 */
	public array $mappings = [];

	/**
	 * CodeGen constructor.
	 *
	 * @param array{string:PHPClosure} $validGlobalFunctions An array of valid global function closures.
	 */
	public function __construct(public array $validGlobalFunctions = [], public array $directAccessFor=[]){}

	private function intercept(string $value): string {
		return "(\$intercept($value))";
	}


	/**
	 * Generates code like `arg1, arg2, arg3` from an argument list node.
	 */
	public function visitArgumentList(ArgumentListNode $node): string {
		$arguments = array_map(fn($argument) => $argument->accept($this), $node->arguments);
		return join(', ', $arguments);
	}

	/**
	 * Generates code like `[element1, element2, element3]` from an array list node.
	 */
	public function visitArrayList(ArrayListNode $node): string {
		$elements = array_map(fn($element) => $element->accept($this), $node->elements);
		return '[' . join(', ', $elements) . ']';
	}

	/**
	 * Generates code like `$left ?? $right` from a coalesce node.
	 */
	public function visitCoalesce(CoalesceNode $node): string {
		$left = $node->left->accept($this);
		$right = $node->right->accept($this);
		return "($left ?? $right)";
	}

	/**
	 * Generates code like `true`, `false`, `123.45`, `"foo bar"`, etc from a literal node.
	 */
	public function visitLiteral(LiteralNode $node): string {
		return '$intercept(' . var_export($node->value, true) . ')';
	}

	/**
	 * Generates code like `$object->member` or `$object->member($arguments)` from a member access node.
	 */
	public function visitMemberAccess(MemberAccessNode $node): string {
		$object = $node->object->accept($this);
		$member = $node->member;

		$this->uses['Kirby\\Toolkit\\Query\\Runtime'] = true;
		$memberStr = var_export($member, true);
		$nullSafe = $node->nullSafe ? 'true' : 'false';

		if($node->arguments) {
			$arguments = $node->arguments->accept($this);
			$member = var_export($member, true);

			return $this->intercept("Runtime::access($object, $memberStr, $nullSafe, $arguments)");
		}

		return $this->intercept("Runtime::access($object, $memberStr, $nullSafe)");
	}

	/**
	 * Generates code like `($condition ? $trueBranch : $falseBranch)` or `($condition ?: $falseBranch)` from a ternary node.
	 */
	public function visitTernary(TernaryNode $node): string {
		$left = $node->condition->accept($this);
		$falseBranch = $node->falseBranch->accept($this);

		if($node->trueBranchIsDefault) {
			return "($left ?: $falseBranch)";
		} else {
			$trueBranch = $node->trueBranch->accept($this);
			return "($left ? $trueBranch : $falseBranch)";
		}
	}

	public function visitVariable(VariableNode $node): string {
		$name = $node->name;
		$namestr = var_export($name, true);

		$key = "_$name";
		if(isset($this->directAccessFor[$name])) {
			return $this->intercept("$$key");
		}

		if(!isset($this->mappings[$key])) {
			$this->mappings[$key] = $this->intercept("match(true) { isset(\$context[$namestr]) => \$context[$namestr], isset(\$functions[$namestr]) => \$functions[$namestr](), default => null }");
		}

		return "\$$key";
	}

	/**
	 * Generates code like `$functions['function']($arguments)` from a global function node.
	 */
	public function visitGlobalFunction(GlobalFunctionNode $node): string {
		$name = $node->name;
		if(!isset($this->validGlobalFunctions[$name])) {
			throw new Exception("Invalid global function $name");
		}

		$arguments = $node->arguments->accept($this);
		$name = var_export($name, true);

		return  $this->intercept($this->intercept("\$functions[$name]") . "($arguments)");
	}

	public function visitClosure(ClosureNode $node): mixed {
		$this->uses['Kirby\\Toolkit\\Query\\Runtime'] = true;

		$names = array_map(fn($n) => $n->name, $node->arguments->arguments);
		$args = array_map(fn(string $n) => "\$_$n", $names);
		$args = join(', ', $args);

		$newDirectAccessFor = array_merge($this->directAccessFor, array_fill_keys($names, true));

		return "fn($args) => " . $node->body->accept(new self($this->validGlobalFunctions, $newDirectAccessFor));
	}
}
