<?php

namespace Kirby\Query;

enum TokenType
{
	case T_STRING;
	case T_INTEGER;
	case T_WHITESPACE;
	case T_IDENTIFIER;
	case T_DOT;
	case T_OPEN_PAREN;
	case T_CLOSE_PAREN;
	case T_OPEN_BRACKET;
	case T_CLOSE_BRACKET;
	case T_QUESTION_MARK;
	case T_TERNARY_DEFAULT; // ?:
	case T_NULLSAFE; // ?.
	case T_COLON;
	case T_COALESCE; // ??
	case T_COMMA;
	case T_EOF;
	case T_TRUE;
	case T_FALSE;
	case T_NULL;
	case T_ARROW;
}
