<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * In-text footnote reference
 *
 * A marker in the text that will become a superscript number,
 * referring to a footnote definition (`Kirby\Text\Markdown\Block\Footnotes`)
 * that will be placed in a list of footnotes at the end of the document.
 *
 * @example
 * That's some text with a footnote.[^1]
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Footnote extends Span
{
	protected const PATTERN = '/^\[\^(.+?)\]/';

	public static function markers(): array
	{
		return ['['];
	}

	public function consume(Phrase $phrase): Node|false
	{
		// the pattern needs a caret right after the `[`; skip the regex for
		// the far more common plain `[` of links and bracketed prose
		if ($phrase->at(1) !== '^') {
			return false;
		}

		$matches = $phrase->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$name = $matches[1];
		$data = $this->data()->get('Footnote', $name);

		if ($data === null) {
			return false;
		}

		$data['count']++;

		if (isset($data['number']) === false) {
			// running number in order of first reference; derived from the
			// per-document store (reset each parse) rather than a counter on
			// this reused component, so numbering never leaks between parses
			$numbered = array_filter(
				$this->data()->get('Footnote'),
				fn (array $footnote): bool => isset($footnote['number']) === true
			);

			$data['number'] = count($numbered) + 1;
		}

		$this->data()->set('Footnote', $name, $data);

		$phrase->take($matches[0]);

		return new Element(
			name:       'sup',
			attributes: [
				'id' => 'fnref' . $data['count'] . ':' . $name
			],
			children:   [
				new Element(
					name: 'a',
					attributes: [
						'href'  => '#fn:' . $name,
						'class' => 'footnote-ref'
					],
					children: [new Text((string)$data['number'])]
				)
			],
			break:      false
		);
	}
}
