<?php

namespace Kirby\Query\Parsers;

use Exception;
use Iterator;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\ArrayListNode;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\GlobalFunctionNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\Node;
use Kirby\Query\AST\TernaryNode;
use Kirby\Query\AST\VariableNode;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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

		$this->tokens = $query;
		$first        = $this->tokens->current();

		if ($first === null) {
			throw new Exception('No tokens found');
		}

		$this->current = $first;
	}

	/**
	 * Move to the next token
	 */
	protected function advance(): Token|null
	{
		if ($this->isAtEnd() === false) {
			$this->previous = $this->current;
			$this->tokens->next();
			$this->current = $this->tokens->current();
		}

		return $this->previous;
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
		// primitives/scalars
		if ($token = $this->matchAny([
			TokenType::T_TRUE,
			TokenType::T_FALSE,
			TokenType::T_NULL,
			TokenType::T_STRING,
			TokenType::T_INTEGER,
			TokenType::T_FLOAT,
		])) {
			return new LiteralNode(value: $token->literal);
		}

		// arrays
		if ($token = $this->match(TokenType::T_OPEN_BRACKET)) {
			return new ArrayListNode(
				elements: $this->consumeList(TokenType::T_CLOSE_BRACKET)
			);
		}

		// global functions and variables
		if ($token = $this->match(TokenType::T_IDENTIFIER)) {
			if ($this->match(TokenType::T_OPEN_PAREN)) {
				return new GlobalFunctionNode(
					name: $token->lexeme,
					arguments: $this->argumentList()
				);
			}

			return new VariableNode(name: $token->lexeme);
		}

		// grouping and closure argument lists
		if ($token = $this->match(TokenType::T_OPEN_PAREN)) {
			$list = $this->consumeList(TokenType::T_CLOSE_PAREN);

			if ($this->match(TokenType::T_ARROW)) {
				$expression = $this->expression();

				/**
				 * Assert that all elements are VariableNodes
				 * @var VariableNode[] $list
				 */
				foreach ($list as $element) {
					if ($element instanceof VariableNode === false) {
						throw new Exception('Expecting only variables in closure argument list.');
					}
				}

				$arguments = array_map(fn ($element) => $element->name, $list);

				return new ClosureNode(
					arguments: $arguments,
					body: $expression
				);
			}

			if (count($list) > 1) {
				throw new Exception('Expecting \"=>\" after closure argument list.');
			}

			// this is just a grouping
			return $list[0];
		}

		throw new Exception('Expect expression');
	}

	/**
	 * Whether the current token is of a specific type
	 */
	protected function check(TokenType $type): bool
	{
		if ($this->isAtEnd() === true) {
			return false;
		}

		return $this->current->is($type);
	}

	/**
	 * Checks for and parses a coalesce expression
	 */
	private function coalesce(): Node
	{
		$node = $this->ternary();

		while ($this->match(TokenType::T_COALESCE)) {
			$node = new CoalesceNode(
				left: $node,
				right: $this->ternary()
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
		string $error
	): Token {
		if ($this->check($type) === true) {
			return $this->advance();
		}

		throw new Exception($error);
	}

	/**
	 * Collect all list element until closing token
	 */
	private function consumeList(TokenType $until): array
	{
		$elements = [];

		while (
			$this->isAtEnd() === false &&
			$this->check($until) === false
		) {
			$elements[] = $this->expression();

			if ($this->match(TokenType::T_COMMA) === false) {
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
		// top-level expression check is for coalescing
		return $this->coalesce();
	}

	/**
	 * Whether the parser has reached the end of the query
	 */
	protected function isAtEnd(): bool
	{
		return $this->current->is(TokenType::T_EOF);
	}

	/**
	 * Move to next token if of specific type
	 */
	protected function match(TokenType $type): Token|false
	{
		if ($this->check($type) === true) {
			return $this->advance();
		}

		return false;
	}

	/**
	 * Move to next token if of any specific type
	 */
	protected function matchAny(array $types): Token|false
	{
		foreach ($types as $type) {
			if ($this->check($type) === true) {
				return $this->advance();
			}
		}

		return false;
	}

	/**
	 * Checks for and parses a member access expression
	 */
	private function memberAccess(): Node
	{
		$object = $this->atomic();

		while ($token = $this->matchAny([
			TokenType::T_DOT,
			TokenType::T_NULLSAFE
		])) {
			if ($member = $this->match(TokenType::T_IDENTIFIER)) {
				$member = $member->lexeme;
			} elseif ($member = $this->match(TokenType::T_INTEGER)) {
				$member = $member->literal;
			} else {
				throw new Exception('Expect property name after "."');
			}

			$object = new MemberAccessNode(
				object: $object,
				member: $member,
				arguments: match ($this->match(TokenType::T_OPEN_PAREN)) {
					false   => null,
					default => $this->argumentList(),
				},
				nullSafe: $token->is(TokenType::T_NULLSAFE)
			);
		}

		return $object;
	}

	/**
	 * Parses the tokenized query into AST node tree
	 */
	public function parse(): Node
	{
		// start parsing chain
		$expression = $this->expression();

		// ensure that we consumed all tokens
		if ($this->isAtEnd() === false) {
			$this->consume(TokenType::T_EOF, 'Expect end of expression');
		}

		return $expression;
	}

	/**
	 * Checks for and parses a ternary expression
	 * (full `a ? b : c` or elvis shorthand `a ?: c`)
	 */
	private function ternary(): Node
	{
		$condition = $this->memberAccess();

		if ($token = $this->matchAny([
			TokenType::T_QUESTION_MARK,
			TokenType::T_TERNARY_DEFAULT
		])) {
			if ($token->is(TokenType::T_TERNARY_DEFAULT) === false) {
				$true = $this->expression();
				$this->consume(TokenType::T_COLON, 'Expect ":" after true branch');
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
