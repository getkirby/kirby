<?php

namespace Kirby\Query\Parser;

use Exception;
use Iterator;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\ArithmeticNode;
use Kirby\Query\AST\ArrayListNode;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\ComparisonNode;
use Kirby\Query\AST\GlobalFunctionNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\LogicalNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\Node;
use Kirby\Query\AST\TernaryNode;
use Kirby\Query\AST\VariableNode;

/**
 * Parses query string by first splitting it into tokens
 * and then matching and consuming tokens to create
 * an abstract syntax tree (AST) of matching nodes
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class Parser
{
	protected Token $current;
	protected Token|null $previous = null;

	/**
	 * @var Iterator<Token>
	 */
	protected Iterator $tokens;

	public function __construct(string|Iterator $query)
	{
		if (is_string($query) === true) {
			$tokenizer = new Tokenizer($query);
			$query     = $tokenizer->tokens();
		}

		$this->tokens  = $query;
		$this->current = $this->tokens->current();
	}

	/**
	 * Move to the next token
	 */
	protected function advance(): Token|null
	{
		if ($this->isAtEnd() === false) {
			$this->previous = $this->current;
			$this->tokens->next();
			$this->current  = $this->tokens->current();
		}

		return $this->previous;
	}

	/**
	 * Parses an array
	 */
	private function array(): ArrayListNode|null
	{
		if ($this->consume(TokenType::T_OPEN_BRACKET)) {
			return new ArrayListNode(
				elements: $this->consumeList(TokenType::T_CLOSE_BRACKET)
			);
		}

		return null;
	}

	/**
	 * Parses a list of arguments
	 */
	private function argumentList(): ArgumentListNode
	{
		return new ArgumentListNode(
			arguments: $this->consumeList(TokenType::T_CLOSE_PAREN)
		);
	}

	/**
	 * Checks for and parses several atomic expressions
	 */
	private function atomic(): Node
	{
		$token   = $this->scalar();
		$token ??= $this->array();
		$token ??= $this->identifier();
		$token ??= $this->grouping();

		if ($token === null) {
			throw new Exception('Expect expression'); // @codeCoverageIgnore
		}

		return $token;
	}

	/**
	 * Checks for and parses a coalesce expression
	 */
	private function coalesce(): Node
	{
		$node = $this->logical();

		while ($this->consume(TokenType::T_COALESCE)) {
			$node = new CoalesceNode(
				left: $node,
				right: $this->logical()
			);
		}

		return $node;
	}

	/**
	 * Collect the next token of a type
	 *
	 * @throws \Exception when next token is not of specified type
	 */
	protected function consume(
		TokenType $type,
		string|false $error = false
	): Token|false {
		if ($this->is($type) === true) {
			return $this->advance();
		}

		if (is_string($error) === true) {
			throw new Exception($error);
		}

		return false;
	}

	/**
	 * Move to next token if of any specific type
	 */
	protected function consumeAny(array $types): Token|false
	{
		foreach ($types as $type) {
			if ($this->is($type) === true) {
				return $this->advance();
			}
		}

		return false;
	}

	/**
	 * Collect all list element until closing token
	 */
	private function consumeList(TokenType $until): array
	{
		$elements = [];

		while (
			$this->isAtEnd() === false &&
			$this->is($until) === false
		) {
			$elements[] = $this->expression();

			if ($this->consume(TokenType::T_COMMA) === false) {
				break;
			}
		}

		// consume the closing token
		$this->consume($until, 'Expect closing bracket after list');

		return $elements;
	}

	/**
	 * Returns the current token
	 */
	public function current(): Token
	{
		return $this->current;
	}

	/**
	 * Convert a full query expression into a node
	 */
	private function expression(): Node
	{
		// Top-level expression should be ternary
		return $this->ternary();
	}

	/**
	 * Parses comparison expressions with proper precedence
	 */
	private function comparison(): Node
	{
		$left = $this->arithmetic();

		while ($token = $this->consumeAny([
			TokenType::T_EQUAL,
			TokenType::T_IDENTICAL,
			TokenType::T_NOT_EQUAL,
			TokenType::T_NOT_IDENTICAL,
			TokenType::T_LESS_THAN,
			TokenType::T_LESS_EQUAL,
			TokenType::T_GREATER_THAN,
			TokenType::T_GREATER_EQUAL
		])) {
			$left = new ComparisonNode(
				left: $left,
				operator: $token->lexeme,
				right: $this->arithmetic()
			);
		}

		return $left;
	}

	/**
	 * Parses a grouping (e.g. closure)
	 */
	private function grouping(): ClosureNode|Node|null
	{
		if ($this->consume(TokenType::T_OPEN_PAREN)) {
			$list = $this->consumeList(TokenType::T_CLOSE_PAREN);

			if ($this->consume(TokenType::T_ARROW)) {
				$expression = $this->expression();

				/**
				 * Assert that all elements are VariableNodes
				 * @var VariableNode[] $list
				 */
				foreach ($list as $element) {
					if ($element instanceof VariableNode === false) {
						throw new Exception('Expecting only variables in closure argument list');
					}
				}

				$arguments = array_map(fn ($element) => $element->name, $list);

				return new ClosureNode(
					arguments: $arguments,
					body: $expression
				);
			}

			if (count($list) > 1) {
				throw new Exception('Expecting "=>" after closure argument list');
			}

			// this is just a grouping
			return $list[0];
		}

		return null;
	}

	/**
	 * Parses an identifier (global functions or variables)
	 */
	private function identifier(): GlobalFunctionNode|VariableNode|null
	{
		if ($token = $this->consume(TokenType::T_IDENTIFIER)) {
			if ($this->consume(TokenType::T_OPEN_PAREN)) {
				return new GlobalFunctionNode(
					name: $token->lexeme,
					arguments: $this->argumentList()
				);
			}

			return new VariableNode(name: $token->lexeme);
		}

		return null;
	}

	/**
	 * Whether the current token is of a specific type
	 */
	protected function is(TokenType $type): bool
	{
		if ($this->isAtEnd() === true) {
			return false;
		}

		return $this->current->is($type);
	}

	/**
	 * Whether the parser has reached the end of the query
	 */
	protected function isAtEnd(): bool
	{
		return $this->current->is(TokenType::T_EOF);
	}

	/**
	 * Checks for and parses a member access expression
	 */
	private function memberAccess(): Node
	{
		$object = $this->atomic();

		while ($token = $this->consumeAny([
			TokenType::T_DOT,
			TokenType::T_NULLSAFE,
			TokenType::T_OPEN_BRACKET
		])) {
			if ($token->is(TokenType::T_OPEN_BRACKET) === true) {
				// For subscript notation, parse the inside as expression…
				$member = $this->expression();

				// …and ensure consuming the closing bracket
				$this->consume(
					TokenType::T_CLOSE_BRACKET,
					'Expect subscript closing bracket'
				);
			} elseif ($member = $this->consume(TokenType::T_IDENTIFIER)) {
				$member = new LiteralNode($member->lexeme);
			} elseif ($member = $this->consume(TokenType::T_INTEGER)) {
				$member = new LiteralNode($member->literal);
			} else {
				throw new Exception('Expect property name after "."');
			}

			$object = new MemberAccessNode(
				object: $object,
				member: $member,
				arguments: match ($this->consume(TokenType::T_OPEN_PAREN)) {
					false   => null,
					default => $this->argumentList(),
				},
				nullSafe: $token->is(TokenType::T_NULLSAFE)
			);
		}

		return $object;
	}

	/**
	 * Parses arithmetic expressions with proper precedence
	 */
	private function arithmetic(): Node
	{
		$left = $this->term();

		while ($token = $this->consumeAny([
			TokenType::T_PLUS,
			TokenType::T_MINUS
		])) {
			$left = new ArithmeticNode(
				left: $left,
				operator: $token->lexeme,
				right: $this->term()
			);
		}

		return $left;
	}

	/**
	 * Parses multiplication, division, and modulo expressions
	 */
	private function term(): Node
	{
		$left = $this->memberAccess();

		while ($token = $this->consumeAny([
			TokenType::T_MULTIPLY,
			TokenType::T_DIVIDE,
			TokenType::T_MODULO
		])) {
			$left = new ArithmeticNode(
				left: $left,
				operator: $token->lexeme,
				right: $this->memberAccess()
			);
		}

		return $left;
	}

	/**
	 * Parses logical expressions with proper precedence
	 */
	private function logical(): Node
	{
		$left = $this->comparison();

		while ($token = $this->consumeAny([
			TokenType::T_AND,
			TokenType::T_OR
		])) {
			$left = new LogicalNode(
				left: $left,
				operator: $token->lexeme,
				right: $this->comparison()
			);
		}

		return $left;
	}

	/**
	 * Parses the tokenized query into AST node tree
	 */
	public function parse(): Node
	{
		// Start parsing chain
		$expression = $this->expression();

		// Ensure that we consumed all tokens
		if ($this->isAtEnd() === false) {
			$this->consume(TokenType::T_EOF, 'Expect end of expression'); // @codeCoverageIgnore
		}

		return $expression;
	}

	private function scalar(): LiteralNode|null
	{
		if ($token = $this->consumeAny([
			TokenType::T_TRUE,
			TokenType::T_FALSE,
			TokenType::T_NULL,
			TokenType::T_STRING,
			TokenType::T_INTEGER,
			TokenType::T_FLOAT,
		])) {
			return new LiteralNode(value: $token->literal);
		}

		return null;
	}

	/**
	 * Checks for and parses a ternary expression
	 * (full `a ? b : c` or elvis shorthand `a ?: c`)
	 */
	private function ternary(): Node
	{
		$condition = $this->coalesce();

		if ($token = $this->consumeAny([
			TokenType::T_QUESTION_MARK,
			TokenType::T_TERNARY_DEFAULT
		])) {
			if ($token->is(TokenType::T_TERNARY_DEFAULT) === false) {
				$true = $this->expression();
				$this->consume(
					type:  TokenType::T_COLON,
					error: 'Expect ":" after true branch'
				);
			}

			return new TernaryNode(
				condition: $condition,
				true: $true ?? null,
				false: $this->expression()
			);
		}

		return $condition;
	}
}
