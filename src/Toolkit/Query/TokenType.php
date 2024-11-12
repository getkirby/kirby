<?php

namespace Kirby\Toolkit\Query;

enum TokenType {
	case STRING;
	case INTEGER;
	case WHITESPACE;
	case IDENTIFIER;
	case DOT;
	case OPEN_PAREN;
	case CLOSE_PAREN;
	case OPEN_BRACKET;
	case CLOSE_BRACKET;
	case QUESTION_MARK;
	case TERNARY_DEFAULT; // ?:
	case NULLSAFE; // ?.
	case COLON;
	case COALESCE; // ??
	case COMMA;
	case EOF;
	case TRUE;
	case FALSE;
	case NULL;
	case ARROW;
}
