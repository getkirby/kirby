<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\Transform;

/**
 * Footnotes definition
 *
 * A footnote definition that will be placed
 * in a list of footnotes at the end of the document.
 * The definition produces no output itself. A matching
 * `Kirby\Text\Markdown\Span\Footnote` references it.
 *
 * Each footnote must have a distinct name. That name will
 * be used to link footnote spans to footnote definitions,
 * but has no effect on the numbering of the footnotes.
 *
 * Footnote definitions can be found anywhere in the document,
 * but footnotes will always be rendered in the order they are
 * linked to in the text.
 *
 * @example
 * [^1]: And that's the footnote.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Footnotes extends Block implements Transform
{
	protected const PATTERN = '/^\[\^(.+?)\]:[ ]?(.*)$/';

	public static function markers(): array
	{
		return ['['];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		// the definition needs `[^`; skip the regex for the common `[link]`
		// and other bracketed lines that share the `[` marker
		if ($line->startsWith('[^') === false) {
			return false;
		}

		$matches = $line->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$label = $matches[1];
		$text  = $matches[2];
		$line->next();

		$interrupted = 0;

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted++;
				$line->next();
				continue;
			}

			// a new footnote definition closes this one
			if (
				$line->marker() === '[' &&
				$line->matches('/^\[\^(.+?)\]:/') === true
			) {
				break;
			}

			if ($interrupted > 0) {
				// once interrupted only indented lines
				// continue the footnote
				if ($line->indent() < 4) {
					break;
				}

				$text .= "\n\n" . $line->text();
			} else {
				$text .= "\n" . $line->text();
			}

			$line->next();
		}

		// register the definition (produces no output itself)
		$this->data()->set('Footnote', $label, [
			'text'   => $text,
			'count'  => 0,
			'number' => null
		]);

		return null;
	}

	/**
	 * Appends the `<div class="footnotes">` section to the document.
	 * An ordered list of the referenced footnote definitions
	 * (sorted by reference number), each with its back-reference links.
	 *
	 * @param list<Node> $nodes
	 * @return list<Node>
	 */
	public function transform(array $nodes): array
	{
		$footnotes = $this->data()->get('Footnote');

		if ($footnotes === []) {
			return $nodes;
		}

		uasort(
			$footnotes,
			fn (array $a, array $b): int => $a['number'] <=> $b['number']
		);

		$items = [];

		foreach ($footnotes as $id => $data) {
			if (isset($data['number']) === true) {
				$items[] = $this->item($id, $data);
			}
		}

		$section = new Element(
			name:       'div',
			attributes: ['class' => 'footnotes'],
			children:   [
				new Element('hr'),
				new Element(name: 'ol', children: $items, multiline: true)
			],
			multiline:  true
		);

		// resolve the freshly built section before adding it
		$nodes[] = $this->parser->resolver()->node($section);

		return $nodes;
	}

	/**
	 * Builds a single footnote `<li>`: the definition's content with the
	 * back-reference links folded into its last paragraph, or appended as a
	 * new one if the content does not end in a paragraph.
	 */
	protected function item(int|string $id, array $data): Element
	{
		$texts = $this->parser->blocks()->parse($data['text']);
		$links = $this->backlinks($id, $data['count']);

		$n    = count($texts) - 1;
		$last = $texts[$n] ?? null;

		if ($last instanceof Element && $last->name === 'p') {
			// fold the back-links into the last paragraph: unwrap it
			// (a name-less element renders without a surrounding tag)
			$last->name = null;
			$texts[$n]  = new Element(
				name:      'p',
				children:  [$last, new Html('&#160;', trusted: true), ...$links],
				multiline: true
			);
		} else {
			$texts[] = new Element(
				name:      'p',
				children:  $links,
				multiline: true
			);
		}

		return new Element(
			name:       'li',
			attributes: ['id' => 'fn:' . $id],
			children:   $texts,
			multiline:  true
		);
	}

	/**
	 * The back-reference links for a footnote: one
	 * `<a class="footnote-backref">` per reference, separated by spaces.
	 *
	 * @return list<Node>
	 */
	protected function backlinks(int|string $id, int $count): array
	{
		$links = [];

		for ($number = 1; $number <= $count; $number++) {
			// separator between links only, not before the first
			if ($number > 1) {
				$links[] = new Text(' ');
			}

			$links[] = new Element(
				name:       'a',
				attributes: [
					'href'  => '#fnref' . $number . ':' . $id,
					'rev'   => 'footnote',
					'class' => 'footnote-backref'
				],
				children:   [new Html('&#8617;', trusted: true)],
				break:      false
			);
		}

		return $links;
	}
}
