<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Orchestrates the Markdown parsing for inline elements
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Spans
{
	public function __construct(
		protected Parser $parser
	) {
	}

	/**
	 * Whether a span parser is disabled in the current context
	 *
	 * @param list<class-string> $disabled
	 */
	protected function disabled(Span $span, array $disabled): bool
	{
		foreach ($disabled as $class) {
			if ($span instanceof $class) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parses a line of text into inline spans
	 *
	 * @param list<class-string> $disabled span types that must not be parsed
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function parse(string $text, array $disabled = []): array
	{
		$grammar = $this->parser->grammar();
		$markers = $grammar->markers();

		// a run without a single marker (very common for the inner text of
		// emphasis, links, headings, …) is one plain text node
		if (strcspn($text, $markers) === strlen($text)) {
			return [$this->text($text)];
		}

		$nodes  = [];
		$phrase = new Phrase($text);

		// scan from marker to marker
		while ($phrase->seek($markers) === true) {
			foreach ($grammar->spans($phrase->marker()) as $span) {
				if (
					$disabled !== [] &&
					$this->disabled($span, $disabled) === true
				) {
					continue;
				}

				$node = $span->consume($phrase);

				// not this span, or a reach-back match that the cursor rejects
				// because it belongs to a later marker
				if ($node === false || $phrase->matched() === false) {
					continue;
				}

				// the new element's deferred content inherits
				// the span types disabled in the current context
				if (
					$disabled !== [] &&
					$node instanceof Element &&
					$node->content !== null
				) {
					$node->omit = array_merge(
						$node->omit,
						$disabled
					);
				}

				$nodes[] = $this->text($phrase->lead());

				if ($node !== null) {
					$nodes[] = $node;
				}

				$phrase->flush();

				continue 2;
			}

			$nodes[] = $this->text($phrase->skip());
		}

		// the trailing text after the last marker
		$nodes[] = $this->text($phrase->context());

		return $nodes;
	}

	/**
	 * Splits $text on $regex and interleaves the given $elements
	 * at each match, keeping the unmatched text as plain text nodes.
	 *
	 * @param list<Node> $elements
	 * @return list<Node>
	 */
	public static function replace(
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
	 * Compiles a run of unmarked text into a plain text leaf,
	 * or a name-less element that splits it on `<br>`.
	 */
	protected function text(string $text): Node
	{
		if (str_contains($text, "\n") === false) {
			return new Text($text);
		}

		$regex = match ($this->parser->breaks) {
			true  => '/[ ]*+\n/',
			false => '/(?:[ ]*+\\\\|[ ]{2,}+)\n/'
		};

		return new Element(
			name:      null,
			children:  static::replace(
				$regex,
				[new Element(name: 'br'), new Text("\n")],
				$text
			),
			multiline: true,
			break:     false
		);
	}
}
