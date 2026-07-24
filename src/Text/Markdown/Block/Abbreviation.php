<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\Transform;

/**
 * Abbreviation definitions can be added anywhere in the document.
 * They are stripped from the final document. Any instance of those
 * words in the document text will become an `<abbr>` HTML tag.
 *
 * @example
 * *[HTML]: Hyper Text Markup Language
 * *[W3C]:  World Wide Web Consortium
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Abbreviation extends LeafBlock implements Transform
{
	protected const string PATTERN = '/^\*\[(.+?)\]:[ ]*(.+?)[ ]*$/';

	public static function markers(): array
	{
		return ['*'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		if ($line->startsWith('*[') === false) {
			return false;
		}

		$matches = $line->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$this->data()->set('Abbreviation', $matches[1], $matches[2]);
		$line->next();

		// the definition itself produces no output
		return null;
	}

	protected function insert(
		Node $node,
		string $abbreviation,
		string $meaning
	): Node {
		// leave verbatim content (inline code, code blocks) untouched
		if (
			$node instanceof Element &&
			($node->name === 'code' || $node->name === 'pre')
		) {
			return $node;
		}

		// descend into children before transforming this node
		if ($node instanceof Element && $node->children !== null) {
			foreach ($node->children as $index => $child) {
				$node->children[$index] = $this->insert(
					node:         $child,
					abbreviation: $abbreviation,
					meaning:      $meaning
				);
			}

			return $node;
		}

		if ($node instanceof Text) {
			$text    = new Text($abbreviation);
			$element = new Element('abbr', ['title' => $meaning], [$text]);

			return new Element(
				name:      null,
				children:  self::replace(
					'/\b' . preg_quote($abbreviation, '/') . '\b/',
					[$element],
					$node->text
				),
				multiline: true
			);
		}

		return $node;
	}

	/**
	 * Splits $text on $regex and interleaves the given $elements
	 * at each match, keeping the unmatched text as plain text nodes.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $elements
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected static function replace(
		string $regex,
		array $elements,
		string $text
	): array {
		$nodes = [];

		while (preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE) === 1) {
			$offset = $matches[0][1];
			$before = substr($text, 0, $offset);
			$after  = substr($text, $offset + strlen($matches[0][0]));

			$nodes[] = new Text($before);

			foreach ($elements as $element) {
				$nodes[] = $element;
			}

			$text = $after;
		}

		$nodes[] = new Text($text);

		return $nodes;
	}

	/**
	 * Wraps every occurrence of each defined abbreviation
	 * across the whole document in an `<abbr title="…">` tag.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function transform(array $nodes): array
	{
		$abbreviations = $this->data()->get('Abbreviation');

		foreach ($abbreviations as $abbreviation => $meaning) {
			foreach ($nodes as $index => $node) {
				$nodes[$index] = $this->insert($node, (string)$abbreviation, $meaning);
			}
		}

		return $nodes;
	}
}
