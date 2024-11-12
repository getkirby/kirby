<?php

namespace Kirby\Toolkit\Query;

use Exception;
use Iterator;
use Kirby\Toolkit\Query\AST\ArgumentList;
use Kirby\Toolkit\Query\AST\ArrayList;
use Kirby\Toolkit\Query\AST\Closure;
use Kirby\Toolkit\Query\AST\Coalesce;
use Kirby\Toolkit\Query\AST\GlobalFunction;
use Kirby\Toolkit\Query\AST\Literal;
use Kirby\Toolkit\Query\AST\MemberAccess;
use Kirby\Toolkit\Query\AST\Node;
use Kirby\Toolkit\Query\AST\Ternary;
use Kirby\Toolkit\Query\AST\Variable;

class Parser extends BaseParser {
	public function __construct(
		Tokenizer|Iterator $source,
	) {
		parent::__construct($source);
	}

	public function parse(): Node {
		$expression = $this->expression();

		// ensure that we consumed all tokens
		if(!$this->isAtEnd())
			$this->consume(TokenType::EOF, 'Expect end of expression.');

		return $expression;
	}

	private function expression(): Node {
		return $this->coalesce();
	}

	private function coalesce(): Node {
		$left = $this->ternary();

		while ($this->match(TokenType::COALESCE)) {
			$right = $this->ternary();
			$left = new Coalesce($left, $right);
		}

		return $left;
	}

	private function ternary(): Node {
		$left = $this->memberAccess();

		if ($tok = $this->matchAny([TokenType::QUESTION_MARK, TokenType::TERNARY_DEFAULT])) {
			if($tok->type === TokenType::TERNARY_DEFAULT) {
				$trueIsDefault = true;
				$trueBranch = null;
				$falseBranch = $this->expression();
			} else {
				$trueIsDefault = false;
				$trueBranch = $this->expression();
				$this->consume(TokenType::COLON, 'Expect ":" after true branch.');
				$falseBranch = $this->expression();
			}

			return new Ternary($left, $trueBranch, $falseBranch, $trueIsDefault);
		}

		return $left;
	}

	private function memberAccess(): Node {
		$left = $this->atomic();

		while ($tok = $this->matchAny([TokenType::DOT, TokenType::NULLSAFE])) {
			$nullSafe = $tok->type === TokenType::NULLSAFE;

			if($right = $this->match(TokenType::IDENTIFIER)) {
				$right = $right->lexeme;
			} else if($right = $this->match(TokenType::INTEGER)) {
				$right = $right->literal;
			} else {
				throw new Exception('Expect property name after ".".');
			}

			if($this->match(TokenType::OPEN_PAREN)) {
				$arguments = $this->argumentList();
				$left = new MemberAccess($left, $right, $arguments, $nullSafe);
			} else {
				$left = new MemberAccess($left, $right, null, $nullSafe);
			}
		}

		return $left;
	}

	private function listUntil(TokenType $until): array {
		$elements = [];

		while (!$this->isAtEnd() && !$this->check($until)) {
			$elements[] = $this->expression();

			if (!$this->match(TokenType::COMMA)) {
				break;
			}
		}

		// consume the closing token
		$this->consume($until, 'Expect closing bracket after list.');

		return $elements;
	}

	private function argumentList(): Node {
		$list = $this->listUntil(TokenType::CLOSE_PAREN);

		return new ArgumentList($list);
	}



	private function atomic(): Node {

		// float numbers
		if ($integer = $this->match(TokenType::INTEGER)) {
			if($this->match(TokenType::DOT)) {
				$fractional = $this->match(TokenType::INTEGER);
				return new Literal(floatval($integer->literal . '.' . $fractional->literal));
			}
			return new Literal($integer->literal);
		}

		// primitives
		if ($token = $this->matchAny([
			TokenType::TRUE,
			TokenType::FALSE,
			TokenType::NULL,
			TokenType::STRING,
		])) {
			return new Literal($token->literal);
		}

		// array literals
		if ($token = $this->match(TokenType::OPEN_BRACKET)) {
			$arrayItems = $this->listUntil(TokenType::CLOSE_BRACKET);

			return new ArrayList($arrayItems);
		}

		// global functions and variables
		if ($token = $this->match(TokenType::IDENTIFIER)) {
			if($this->match(TokenType::OPEN_PAREN)) {
				$arguments = $this->argumentList();
				return new GlobalFunction($token->lexeme, $arguments);
			}

			return new Variable($token->lexeme);
		}

		// grouping and closure argument lists
		if ($token = $this->match(TokenType::OPEN_PAREN)) {
			$list = $this->listUntil(TokenType::CLOSE_PAREN);

			if($this->match(TokenType::ARROW)) {
				$expression = $this->expression();
				// check if all elements are variables
				foreach($list as $element) {
					if(!$element instanceof Variable) {
						throw new Exception('Expecting only variables in closure argument list.');
					}
				}
				$arguments = new ArgumentList($list);
				return new Closure($arguments, $expression);
			} else {
				if(count($list) > 1) {
					throw new Exception('Expecting \"=>\" after closure argument list.');
				} else {
					// this is just a grouping
					return $list[0];
				}
			}
		}

		throw new Exception('Expect expression.');
	}
}
