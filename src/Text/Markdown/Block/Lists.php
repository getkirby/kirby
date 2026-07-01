<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Ordered (`ol`) and unordered (`ul`) lists
 *
 * Unordered lists use asterisks, pluses, and hyphens  as list markers.
 * Ordered lists use numbers followed by periods. The actual numbers
 * you use to mark the list have no effect on the HTML output.
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
class Lists extends Block
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

		$this->items($line, $element, $item, $indent, $marker, $pattern, $paragraph);

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
			block:     true,
			omit:      true
		);
	}

	/**
	 * Reads the list's remaining lines off
	 * the cursor into `<li>` items
	 */
	protected function items(
		Line $line,
		Element $element,
		Element $item,
		int $indent,
		string $marker,
		string $pattern,
		Element|null $paragraph
	): void {
		$loose       = false;
		$interrupted = 0;

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

			// a new item of the same list type
			if (
				$line->indent() < $required &&
				($m = $line->match($pattern)) !== null
			) {
				if ($interrupted > 0) {
					$item->content[] = '';
					$loose           = true;
					$interrupted     = 0;
				}

				$indent = $line->indent();
				$item   = $this->item([$m[1] ?? '']);

				$element->children[] = $item;

				$line->next();
				continue;
			}

			// a different list starts here: end this one
			if ($line->indent() < $required && $this->detect($line) !== null) {
				break;
			}

			// a reference definition inside the list
			// (registered, produces no item;
			// the reference parser consumes its own line off the cursor)
			if (
				$line->marker() === '[' &&
				($reference = $this->parser->grammar()->block(Reference::class)) &&
				$reference->consume($line, $paragraph) !== false
			) {
				continue;
			}

			// indented content of the current item
			if ($line->indent() >= $required) {
				if ($interrupted > 0) {
					$item->content[] = '';
					$loose           = true;
					$interrupted     = 0;
				}

				$item->content[] = substr($line->body(), $required);

				$line->next();
				continue;
			}

			// lazy continuation of the current item: strip up to $required
			// leading spaces (what the possessive `^[ ]{0,N}+` matched)
			if ($interrupted === 0) {
				$body            = $line->body();
				$item->content[] = substr($body, min(strspn($body, ' '), $required));

				$line->next();
				continue;
			}

			// anything else ends the list
			break;
		}

		// loose list: every item ends with a blank line
		if ($loose === true) {
			foreach ($element->children as $li) {
				$last = $li->content === [] ? null : $li->content[array_key_last($li->content)];

				if ($last !== '') {
					$li->content[] = '';
				}
			}
		}
	}
}
