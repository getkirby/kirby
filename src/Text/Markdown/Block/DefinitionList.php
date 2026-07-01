<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\Transform;

/**
 * Definition list
 *
 * A simple definition list is made of a single-line term
 * followed by a colon and the definition for that term.
 *
 * @example
 * Orange
 * :   The fruit of an evergreen tree of the genus Citrus.
 *
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class DefinitionList extends Block implements Transform
{
	public static function markers(): array
	{
		return [':'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if ($paragraph === null) {
			return false;
		}

		// turn the open paragraph into the term list in place
		$terms = explode("\n", $paragraph->content);

		$paragraph->name     = 'dl';
		$paragraph->children = [];
		$paragraph->content  = null;

		foreach ($terms as $term) {
			$paragraph->children[] = new Element(
				name:      'dt',
				multiline: true,
				content:   $term
			);
		}

		// the opening `:` line is the first definition;
		// it inherits whether the paragraph above was
		// interrupted by a blank line
		$item        = $this->dd($line, $paragraph, $line->isBlank(offset: -1));
		$interrupted = false;
		$line->next();

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted = true;
				$line->next();
				continue;
			}

			// a `:` line opens a new definition
			if ($line->marker() === ':') {
				$item        = $this->dd($line, $paragraph, $interrupted);
				$interrupted = false;
				$line->next();
				continue;
			}

			// after a blank, an unindented line ends the list
			if ($interrupted === true && $line->indent() === 0) {
				break;
			}

			// after a blank, the current definition turns block-level (loose)
			if ($interrupted === true) {
				$item->block    = true;
				$item->content .= "\n\n";
				$interrupted    = false;
			}

			$item->content .= "\n" . substr($line->body(), min($line->indent(), 4));
			$line->next();
		}

		// register the emitted list so transform() can skip the sibling-merge
		// walk unless at least two lists exist to merge
		$this->data()->set('DefinitionList', (string)spl_object_id($paragraph), true);

		return $paragraph;
	}

	/**
	 * Appends a `<dd>` definition for the current `:` line
	 * to the list and returns it.
	 */
	protected function dd(
		Line $line,
		Element $dl,
		bool $interrupted
	): Element {
		$text = trim($line->slice(1));

		$dd = new Element(
			name:      'dd',
			multiline: true,
			content:   $text
		);

		if ($interrupted === true) {
			$dd->block = true;
		}

		$dl->children[] = $dd;

		return $dd;
	}

	/**
	 * Merges adjacent `<dl>` siblings into a single list.
	 *
	 * @param list<Node> $nodes
	 * @return list<Node>
	 */
	public function transform(array $nodes): array
	{
		if (count($this->data()->get('DefinitionList')) < 2) {
			return $nodes;
		}

		$merged = [];
		$last   = null;

		foreach ($nodes as $node) {
			// fold this list into the previous one
			if (
				$node instanceof Element &&
				$node->name === 'dl' &&
				$last instanceof Element &&
				$last->name === 'dl'
			) {
				$last->children = [
					...$last->children ?? [],
					...$node->children ?? []
				];

				continue;
			}

			$merged[] = $node;
			$last     = $node;
		}

		return $merged;
	}
}
