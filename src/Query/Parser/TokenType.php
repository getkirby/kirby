<?php

namespace Kirby\Query\Parser;

/**
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
enum TokenType
{
	case T_AND; // AND or &&
	case T_ARROW;
	case T_CLOSE_BRACKET;
	case T_CLOSE_PAREN;
	case T_COALESCE; // ??
	case T_COLON;
	case T_COMMA;
	case T_DIVIDE; // /
	case T_DOT;
	case T_EOF;
	case T_EQUAL; // ==
	case T_FALSE;
	case T_FLOAT;
	case T_GREATER_EQUAL; // >=
	case T_GREATER_THAN; // >
	case T_IDENTICAL; // ===
	case T_IDENTIFIER;
	case T_INTEGER;
	case T_LESS_EQUAL; // <=
	case T_LESS_THAN; // <
	case T_MINUS; // -
	case T_MODULO; // %
	case T_MULTIPLY; // *
	case T_NOT; // !
	case T_NOT_EQUAL; // !=
	case T_NOT_IDENTICAL; // !==
	case T_NULL;
	case T_NULLSAFE; // ?.
	case T_OPEN_BRACKET;
	case T_OPEN_PAREN;
	case T_OR;  // OR or ||
	case T_PLUS; // +
	case T_QUESTION_MARK;
	case T_STRING;
	case T_TERNARY_DEFAULT; // ?:
	case T_TRUE;
	case T_WHITESPACE;
}
