<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\Transform;

/**
 * Footnotes definition
 *
 * A footnote definition that will be placed
 * in a list of footnotes at the end of the document.
 * The definition produces no output itself. A matching
 * `Kirby\Text\Markdown\Inline\Footnote` references it.
 *
 * Each footnote must have a distinct name. That name will
 * be used to link footnote inlines to footnote definitions,
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
class Footnotes extends LeafBlock implements Transform
{
	protected const string PATTERN = '/^\[\^(.+?)\]:[ ]?(.*)$/';

	public static function markers(): array
	{
		return ['['];
	}

	/**
	 * The back-reference links for a footnote
	 * per reference, separated by spaces.
	 *
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected function backlinks(int|string $id, int $count): array
	{
		$links = [];

		for ($i = 1; $i <= $count; $i++) {
			if ($i > 1) {
				$links[] = new Text(' ');
			}

			$links[] = new Element(
				name:       'a',
				attributes: [
					'href'  => '#fnref' . $i . ':' . $id,
					'rev'   => 'footnote',
					'class' => 'footnote-backref'
				],
				children:   [new Html('&#8617;', trusted: true)],
				break:      false
			);
		}

		return $links;
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		// the definition needs `[^`; skip for the common `[link]`
		// and other lines that share the `[` marker
		if ($line->startsWith('[^') === false) {
			return false;
		}

		$matches = $line->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$text = $matches[2];
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
		$this->data()->set('Footnote', $matches[1], [
			'text'   => $text,
			'count'  => 0,
			'number' => null
		]);

		return null;
	}

	/**
	 * Builds a single footnote `<li>`: the definition's content
	 * with the back-reference links.
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
	 * Appends the `<div class="footnotes">` section to the document.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 * @return list<\Kirby\Text\Markdown\AST\Node>
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
}
