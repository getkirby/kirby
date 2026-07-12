<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\Inline\BracketedInline;
use Kirby\Text\Markdown\Inline\DelimitedInline;
use Kirby\Text\Markdown\Parser\Brackets;
use Kirby\Text\Markdown\Parser\FlatStack;
use Kirby\Text\Markdown\Parser\Grammar;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Parser\Stack;
use Kirby\Text\Markdown\Parser\Text;

/**
 * Orchestrates the Markdown parsing for inline elements
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Inlines
{
	protected Brackets $brackets;
	protected Grammar $grammar;
	protected string $markers;
	protected string|null $stacking = null;
	protected Text $text;

	public function __construct(
		protected Parser $parser
	) {
		$this->text     = new Text($parser->breaks);
		$this->grammar  = $parser->grammar();
		$this->brackets = new Brackets($parser, $this->text);

		// `]` closes a link/image but is no inline's own marker,
		// so add it to the set that halts the scan
		$this->markers = $this->grammar->markers() . ']';
	}

	/**
	 * Dispatches the marker to the inlines registered for it
	 * and lets the first one that consumes the phrase push
	 * its node onto the stack.
	 */
	protected function dispatch(
		Phrase $phrase,
		Stack $stack,
		string $marker
	): bool {
		foreach ($this->grammar->inlines($marker) as $inline) {
			// link and image brackets are resolved by the stack
			if ($inline instanceof BracketedInline) {
				continue;
			}

			$node = $inline->consume($phrase);

			// not this inline, or a reach-back match the cursor rejects
			// because it belongs to a later marker
			if ($node === false || $phrase->matched() === false) {
				continue;
			}

			$text = $this->text->build($phrase->lead());
			$stack->add($text);

			if ($node !== null) {
				$stack->add($node);
			}

			$phrase->flush();
			return true;
		}

		return false;
	}

	/**
	 * Parses a line of text into inline nodes.
	 *
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function parse(string $text): array
	{
		// a line without a single marker is one plain text node
		if (strcspn($text, $this->markers) === strlen($text)) {
			return [$this->text->build($text)];
		}

		$stack  = $this->stack($text);
		$phrase = new Phrase($text);

		// scan from marker to marker
		while ($phrase->seek($this->markers) === true) {
			$marker = $phrase->marker();

			if ($marker === ']') {
				$this->brackets->close($phrase, $stack);
				continue;
			}

			if ($this->dispatch($phrase, $stack, $marker) === true) {
				continue;
			}

			if ($marker === '[') {
				$this->brackets->open($phrase, $stack, '[');
				continue;
			}

			if ($marker === '!' && $phrase->at(1) === '[') {
				$this->brackets->open($phrase, $stack, '![');
				continue;
			}

			// an unclaimed marker: keep it as literal text
			$text = $this->text->build($phrase->skip());
			$stack->add($text);
		}

		// the trailing text after the last marker
		$text = $this->text->build($phrase->remaining());
		$stack->add($text);

		return $stack->flatten();
	}

	/**
	 * Returns the stack for a line of text.
	 */
	protected function stack(string $text): Stack
	{
		if ($this->stacking === null) {
			$this->stacking = ']';

			foreach (str_split($this->grammar->markers()) as $marker) {
				foreach ($this->grammar->inlines($marker) as $inline) {
					if (
						$inline instanceof DelimitedInline ||
						$inline instanceof BracketedInline
					) {
						$this->stacking .= $marker;
						break;
					}
				}
			}
		}

		return strcspn($text, $this->stacking) === strlen($text)
			? new FlatStack()
			: new Stack();
	}
}
