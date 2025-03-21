<?php

namespace Kirby\Query\Parser;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 */
enum TokenType
{
	case T_IDENTIFIER;
	case T_STRING;
	case T_INTEGER;
	case T_FLOAT;
	case T_TRUE;
	case T_FALSE;
	case T_NULL;
	case T_DOT;
	case T_COLON;
	case T_QUESTION_MARK;
	case T_OPEN_PAREN;
	case T_CLOSE_PAREN;
	case T_OPEN_BRACKET;
	case T_CLOSE_BRACKET;
	case T_TERNARY_DEFAULT; // ?:
	case T_NULLSAFE; // ?.
	case T_COALESCE; // ??
	case T_COMMA;
	case T_ARROW;
	case T_WHITESPACE;
	case T_EOF;
}
