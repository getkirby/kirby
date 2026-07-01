<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Emphasis and strong emphasis
 *
 * @example
 * This wil be an *em* tag.
 * This wil be an _em_ tag.
 * This wil be a **strong** tag.
 * This wil be a __strong__ tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Emphasis extends Span
{
	/**
	 * @var array<string, string>
	 */
	public const STRONG_REGEX = [
		'*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*+[*])+?)[*]{2}(?![*])/s',
		'_' => '/^__((?:\\\\_|[^_]|_[^_]*+_)+?)__(?!_)/us',
	];

	/**
	 * @var array<string, string>
	 */
	public const EM_REGEX = [
		'*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
		'_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
	];

	public static function markers(): array
	{
		return ['*', '_'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		if ($phrase->at(1) === '') {
			return false;
		}

		$marker = $phrase->marker();

		if (
			$phrase->at(1) === $marker &&
			($matches = $phrase->match(self::STRONG_REGEX[$marker])) !== null
		) {
			$emphasis = 'strong';
		} elseif (($matches = $phrase->match(self::EM_REGEX[$marker])) !== null) {
			$emphasis = 'em';
		} else {
			return false;
		}

		$phrase->take(strlen($matches[0]));

		return new Element(
			name:      $emphasis,
			multiline: true,
			break:     false,
			content:   $matches[1]
		);
	}
}
