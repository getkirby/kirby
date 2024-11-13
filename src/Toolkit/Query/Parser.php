<?php

namespace Kirby\Toolkit\Query;

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

class Parser extends BaseParser
{
	public function parse(): Node
	{
		$expression = $this->expression();

		// ensure that we consumed all tokens
		if ($this->isAtEnd() === false) {
			$this->consume(TokenType::T_EOF, 'Expect end of expression.');
		}

		return $expression;
	}

	private function expression(): Node
	{
		return $this->coalesce();
	}

	private function coalesce(): Node
	{
		$left = $this->ternary();

		while ($this->match(TokenType::T_COALESCE)) {
			$right = $this->ternary();
			$left  = new CoalesceNode($left, $right);
		}

		return $left;
	}

	private function ternary(): Node
	{
		$left = $this->memberAccess();

		if ($tok = $this->matchAny([TokenType::T_QUESTION_MARK, TokenType::T_TERNARY_DEFAULT])) {
			if ($tok->type === TokenType::T_TERNARY_DEFAULT) {
				$trueIsDefault = true;
				$trueBranch    = null;
			} else {
				$trueIsDefault = false;
				$trueBranch    = $this->expression();
				$this->consume(TokenType::T_COLON, 'Expect ":" after true branch.');
			}

			$falseBranch = $this->expression();

			return new TernaryNode(
				$left,
				$trueBranch,
				$falseBranch,
				$trueIsDefault
			);
		}

		return $left;
	}

	private function memberAccess(): Node
	{
		$left = $this->atomic();

		while ($tok = $this->matchAny([TokenType::T_DOT, TokenType::T_NULLSAFE])) {
			$nullSafe = $tok->type === TokenType::T_NULLSAFE;

			if ($right = $this->match(TokenType::T_IDENTIFIER)) {
				$right = $right->lexeme;
			} elseif ($right = $this->match(TokenType::T_INTEGER)) {
				$right = $right->literal;
			} else {
				throw new Exception('Expect property name after ".".');
			}

			if ($this->match(TokenType::T_OPEN_PAREN)) {
				$arguments = $this->argumentList();
			}

			$left = new MemberAccessNode(
				$left,
				$right,
				$arguments ?? null,
				$nullSafe
			);
		}

		return $left;
	}

	private function listUntil(TokenType $until): array
	{
		$elements = [];

		while ($this->isAtEnd() === false && $this->check($until) === false) {
			$elements[] = $this->expression();

			if ($this->match(TokenType::T_COMMA) == false) {
				break;
			}
		}

		// consume the closing token
		$this->consume($until, 'Expect closing bracket after list.');

		return $elements;
	}

	private function argumentList(): Node
	{
		$list = $this->listUntil(TokenType::T_CLOSE_PAREN);

		return new ArgumentListNode($list);
	}

	private function atomic(): Node
	{
		// float numbers
		if ($integer = $this->match(TokenType::T_INTEGER)) {
			if ($this->match(TokenType::T_DOT)) {
				$fractional = $this->match(TokenType::T_INTEGER);
				return new LiteralNode((float)($integer->literal . '.' . $fractional->literal));
			}

			return new LiteralNode($integer->literal);
		}

		// primitives
		if ($token = $this->matchAny([
			TokenType::T_TRUE,
			TokenType::T_FALSE,
			TokenType::T_NULL,
			TokenType::T_STRING,
		])) {
			return new LiteralNode($token->literal);
		}

		// array literals
		if ($token = $this->match(TokenType::T_OPEN_BRACKET)) {
			$arrayItems = $this->listUntil(TokenType::T_CLOSE_BRACKET);

			return new ArrayListNode($arrayItems);
		}

		// global functions and variables
		if ($token = $this->match(TokenType::T_IDENTIFIER)) {
			if ($this->match(TokenType::T_OPEN_PAREN)) {
				$arguments = $this->argumentList();
				return new GlobalFunctionNode($token->lexeme, $arguments);
			}

			return new VariableNode($token->lexeme);
		}

		// grouping and closure argument lists
		if ($token = $this->match(TokenType::T_OPEN_PAREN)) {
			$list = $this->listUntil(TokenType::T_CLOSE_PAREN);

			if ($this->match(TokenType::T_ARROW)) {
				$expression = $this->expression();

				/**
				 * Assert that all elements are VariableNodes
				 * @var VariableNode[] $list
				 */
				foreach($list as $element) {
					if ($element instanceof VariableNode === false) {
						throw new Exception('Expecting only variables in closure argument list.');
					}
				}

				$arguments = array_map(fn ($element) => $element->name, $list);
				return new ClosureNode($arguments, $expression);
			}

			if (count($list) > 1) {
				throw new Exception('Expecting \"=>\" after closure argument list.');
			}

			// this is just a grouping
			return $list[0];
		}

		throw new Exception('Expect expression.');
	}
}
