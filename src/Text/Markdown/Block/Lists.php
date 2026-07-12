<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Ordered (`ol`) and unordered (`ul`) lists
 *
 * @example
 * -   Red
 * -   Green
 * -   Blue
 *
 * 1.  Bird
 * 2.  McHale
 * 3.  Parish
 *
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Lists extends ContainerBlock
{
	public static function markers(): array
	{
		return ['*', '+', '-', ...range('0', '9')];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$detected = $this->detect($line);

		if ($detected === null) {
			return false;
		}

		[$name, $matches] = $detected;

		$indent = strlen($matches[2]);

		if ($indent >= 5) {
			$indent -= 1;
			$matches[1] = substr($matches[1], 0, -$indent);
			$matches[3] = str_repeat(' ', $indent) . $matches[3];
		} elseif ($indent === 0) {
			$matches[1] .= ' ';
		}

		$marker  = $matches[1]; // marker incl. trailing spaces
		$bare    = strstr($marker, ' ', true);   // without trailing spaces
		$indent  = $line->indent();
		$type    = $name === 'ul' ? $bare : substr($bare, -1);
		$element = new Element(name: $name, children: [], multiline: true);

		// an item whose first line carries no content cannot interrupt a
		// paragraph (an empty list item never interrupts)
		if (
			trim($matches[3] ?? '') === '' &&
			$paragraph !== null &&
			$line->isBlank(offset: -1) === false
		) {
			return false;
		}

		// matches a subsequent line that opens a new item of this list
		$pattern = $name === 'ol'
			? '/^[0-9]++' . preg_quote($type, '/') . '(?:[ ]++(.*)|$)/'
			: '/^' . preg_quote($type, '/') . '(?:[ ]++(.*)|$)/';

		if ($name === 'ol') {
			$start = ltrim(strstr($marker, $type, true), '0') ?: '0';

			if ($start !== '1') {
				// a non-1 start must not interrupt an open paragraph
				if (
					$paragraph !== null &&
					$line->isBlank(offset: -1) === false
				) {
					return false;
				}

				$element->attributes = ['start' => $start];
			}
		}

		$item = $this->item(($matches[3] ?? '') !== '' ? [$matches[3]] : []);
		$element->children[] = $item;

		$line->next();

		$loose = $this->items($line, $element, $item, $indent, $marker, $pattern, $paragraph);

		$this->resolve($element, $loose);

		return $element;
	}

	/**
	 * Detects a list opener on the line.
	 * Returns `[type, matches]` for any marker.
	 *
	 * @return array{0: string, 1: array<int, string>}|null
	 */
	protected function detect(Line $line): array|null
	{
		[$name, $pattern] = $line->marker() <= '-'
			? ['ul', '[*+-]']
			: ['ol', '[0-9]{1,9}+[.\)]'];

		$matches = $line->match('/^(' . $pattern . '([ ]++|$))(.*+)/');

		if ($matches === null) {
			return null;
		}

		return [$name, $matches];
	}

	/**
	 * Builds a `<li>` item seeded with the given block content lines.
	 *
	 * @param list<string> $content
	 */
	protected function item(array $content): Element
	{
		return new Element(
			name:      'li',
			multiline: true,
			content:   $content,
			block:     true
		);
	}

	/**
	 * Reads the list's remaining lines off the cursor into `<li>` items,
	 * returning whether two items were separated by a blank line (which
	 * makes the whole list loose).
	 */
	protected function items(
		Line $line,
		Element $element,
		Element $item,
		int $indent,
		string $marker,
		string $pattern,
		Element|null $paragraph
	): bool {
		// the list's own indentation
		$base        = $indent;
		$loose       = false;
		$interrupted = 0;

		$grammar = $this->parser->grammar();
		$hr      = $grammar->block(ThematicBreak::class);
		$def     = $grammar->block(LinkDefinition::class);

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted++;
				$line->next();
				continue;
			}

			// a blank line right after an item
			// that never got content closes the list
			if ($interrupted > 0 && $item->content === []) {
				break;
			}

			$required = $indent + strlen($marker);

			// a thematic break ends the list rather than opening a new item,
			// even though `* * *` / `- - -` match the item marker; the
			// thematic break has precedence
			if (
				$line->indent() < $required &&
				$hr?->detects($line) === true
			) {
				break;
			}

			// a new item of the same list type; its marker may be indented
			// at most three spaces past the list's own indentation
			if (
				$line->indent() < $required &&
				$line->indent() <= $base + 3 &&
				($m = $line->match($pattern)) !== null
			) {
				// a blank line between two items makes the list loose
				if ($interrupted > 0) {
					$loose       = true;
					$interrupted = 0;
				}

				$indent = $line->indent();
				$item   = $this->item([$m[1] ?? '']);

				$element->children[] = $item;

				$line->next();
				continue;
			}

			// a different list starts here: end this one
			if (
				$line->indent() < $required &&
				$this->detect($line) !== null
			) {
				break;
			}

			// a reference definition inside the list
			// (registered, produces no item;
			// the reference parser consumes its own line off the cursor)
			if (
				$line->marker() === '[' &&
				$def !== null &&
				$def->consume($line, $paragraph) !== false
			) {
				continue;
			}

			// indented content of the current item
			if ($line->indent() >= $required) {
				// keep every interior blank line verbatim; whether they
				// make the list loose is decided later, when the item's
				// content is parsed into blocks
				for (; $interrupted > 0; $interrupted--) {
					$item->content[] = '';
				}

				$item->content[] = $line->content($required);

				$line->next();
				continue;
			}

			// lazy continuation of the current item, but only as paragraph
			// continuation text: strip up to $required leading spaces (what
			// the possessive `^[ ]{0,N}+` matched). A line that starts a new
			// block (a thematic break, heading, …) ends the list instead
			if ($interrupted === 0) {
				$candidate = $line->content(min($line->indent(), $required));

				// an item that began with a blank line takes this as its
				// first content; otherwise it must extend a trailing paragraph
				if (
					$item->content === [] ||
					$this->isLazyContinuation($item->content, $candidate) === true
				) {
					$item->content[] = $candidate;
					$line->next();
					continue;
				}
			}

			// anything else ends the list; hand any pending blank lines
			// back to the enclosing block, where they may separate this
			// list from following content (making that block loose)
			if ($interrupted > 0) {
				$line->back($interrupted);
			}

			break;
		}

		return $loose;
	}

	/**
	 * Parses each item's collected lines into block nodes and decides the
	 * list's looseness: loose when the items were separated by blank lines
	 * or when any item's own blocks are. A tight list then drops the `<p>`
	 * wrapper around every top-level paragraph so items render inline.
	 */
	protected function resolve(Element $element, bool $loose): void
	{
		$parsed = [];

		/** @var list<Element> $items */
		$items = $element->children;

		foreach ($items as $index => $item) {
			[$nodes, $itemLoose] = $this->parser->blocks()->item((array)$item->content);

			$parsed[$index] = $nodes;
			$loose          = $loose || $itemLoose;
		}

		foreach ($items as $index => $item) {
			$nodes = $parsed[$index];

			if ($loose === false) {
				foreach ($nodes as $node) {
					// a name-less element renders its children
					// without a surrounding tag
					if ($node instanceof Element && $node->name === 'p') {
						$node->name = null;
					}
				}
			}

			$item->content  = null;
			$item->children = $nodes;
		}
	}
}
