<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\Transform;
use Kirby\Text\Markdown\Spans;

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
class Abbreviation extends Block implements Transform
{
	protected const PATTERN = '/^\*\[(.+?)\]:[ ]*(.+?)[ ]*$/';

	public static function markers(): array
	{
		return ['*'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		// the definition needs `*[`; skip the regex for the common `*emphasis*`
		// and `* list item` lines that share the `*` marker
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

	/**
	 * Wraps every occurrence of each defined abbreviation across the whole
	 * document in an `<abbr title="…">` tag. Runs as a document transform
	 * once the tree is resolved, rather than per inline text run.
	 *
	 * @param list<Node> $nodes
	 * @return list<Node>
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
				children:  Spans::replace(
					'/\b' . preg_quote($abbreviation, '/') . '\b/',
					[$element],
					$node->text
				),
				multiline: true
			);
		}

		return $node;
	}
}
