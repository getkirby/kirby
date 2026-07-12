<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\SoftBreak;
use Kirby\Text\Markdown\AST\Text;

/**
 * Delimiter stack that resolves runs of brackets, * or
 * other characters  while inline scanning. A delimiter run
 * left unmatched is turned back into literal text.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Stack
{
	/**
	 * Open bracket cells (`[` / `![`), LIFO stack
	 *
	 * @var list<object>
	 */
	protected array $brackets = [];

	protected object $head;
	protected object $tail;

	protected object|null $first = null;
	protected object|null $last  = null;

	public function __construct()
	{
		$this->head = $this->cell(null);
		$this->tail = $this->head;
	}

	public function add(Node $node): void
	{
		$cell = $this->append($node);

		if ($node instanceof Delimiter) {
			$cell->pdelim = $this->last;

			if ($this->last !== null) {
				$this->last->ndelim = $cell;
			} else {
				$this->first = $cell;
			}

			$this->last = $cell;
		}
	}

	protected function append(Node $node): object
	{
		$cell             = $this->cell($node);
		$cell->prev       = $this->tail;
		$this->tail->next = $cell;
		$this->tail       = $cell;

		return $cell;
	}

	protected function cell(Node|null $node): object
	{
		return (object)[
			'node'    => $node,
			'prev'    => null,
			'next'    => null,
			'pdelim'  => null,
			'ndelim'  => null,
			'bracket' => null,
			'active'  => true,
			'content' => 0,
			'floor'   => null
		];
	}

	/**
	 * @param callable(list<\Kirby\Text\Markdown\AST\Node>): \Kirby\Text\Markdown\AST\Element $build
	 */
	public function close(callable $build): void
	{
		$opener = array_pop($this->brackets);

		// pair the delimiters that lie inside the link text first, bounded
		// to this opener, so the children are fully resolved
		$this->delimiters($opener->floor);

		$children = [];

		for ($cell = $opener->next; $cell !== null; $cell = $cell->next) {
			$children[] = self::materialize($cell->node);
		}

		// splice the gathered inlines out of the tree, reusing the opener
		// cell to carry the finished element
		$opener->node    = $build($children);
		$opener->bracket = null;
		$opener->next    = null;
		$this->tail      = $opener;

		// the emphasis delimiters inside the link are gone with it
		$this->last = $opener->floor;

		if ($opener->floor === null) {
			$this->first = null;
		} else {
			$opener->floor->ndelim = null;
		}

		// no links within links: a formed link disables every earlier
		// link opener (`[`) still on the stack. Image openers (`![`) stay
		// active, since an image may well contain a link
		if ($opener->node->name === 'a') {
			foreach ($this->brackets as $bracket) {
				if ($bracket->bracket === '[') {
					$bracket->active = false;
				}
			}
		}
	}

	public function drop(): void
	{
		array_pop($this->brackets);
	}

	/**
	 * Resolves the delimiter runs on the stack
	 * into their DelimitedInline elements.
	 */
	protected function delimiters(object|null $bottom): void
	{
		$closer = $bottom === null ? $this->first : $bottom->ndelim;

		// the lowest opener still worth inspecting, keyed per marker and
		// closer length mod 3 — keeps the walk linear
		$bottoms = [];

		while ($closer !== null) {
			$close = $closer->node;

			if ($close->canClose === false) {
				$closer = $closer->ndelim;
				continue;
			}

			$key     = $close->marker . ($close->original % 3);
			$limit   = $bottoms[$key] ?? $bottom;
			$opener  = $closer->pdelim;
			$match   = null;
			$pairing = null;

			// walk back for the nearest compatible opener,
			// never crossing the bound
			while (
				$opener !== null &&
				$opener !== $limit &&
				$opener !== $bottom
			) {
				$open = $opener->node;

				if (
					$open->canOpen === true &&
					$open->marker === $close->marker &&
					$open->inline === $close->inline
				) {
					$pair = $close->inline->pair(min($open->length, $close->length));

					if (
						$pair !== null &&
						$close->inline->ruleOfThree($open, $close) === false &&
						(
							$close->inline->rejectsWhitespace($open, $close) === false ||
							$this->hasWhitespaceBetween($opener, $closer) === false
						)
					) {
						$match   = $opener;
						$pairing = $pair;
						break;
					}
				}

				$opener = $opener->pdelim;
			}

			if ($match !== null && $pairing !== null) {
				$opener       = $match;
				$open         = $opener->node;
				[$use, $name] = $pairing;

				// wrap the nodes strictly between the two runs; inner pairs
				// were already resolved (closers advance forward)
				$children = [];

				for ($cell = $opener->next; $cell !== $closer; $cell = $cell->next) {
					$children[] = self::materialize($cell->node);
				}

				$wrap = $this->cell(
					new Element(
						name:      $name,
						children:  $children,
						multiline: true,
						break:     false
					)
				);

				$wrap->prev     = $opener;
				$wrap->next     = $closer;
				$opener->next   = $wrap;
				$closer->prev   = $wrap;
				$opener->ndelim = $closer;
				$closer->pdelim = $opener;

				$open->length  -= $use;
				$close->length -= $use;

				if ($open->length === 0) {
					$this->unlink($opener);
				}

				if ($close->length === 0) {
					$next = $closer->ndelim;
					$this->unlink($closer);
					$closer = $next;
				}

				continue;
			}

			// no opener: raise the bottom, drop the run as an opener
			$bottoms[$key] = $closer->pdelim;
			$next          = $closer->ndelim;

			if ($close->canOpen === false) {
				$this->unthread($closer);
			}

			$closer = $next;
		}
	}

	/**
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function flatten(): array
	{
		$this->delimiters(null);

		$nodes = [];

		for ($cell = $this->head->next; $cell !== null; $cell = $cell->next) {
			$nodes[] = self::materialize($cell->node);
		}

		return $nodes;
	}

	/**
	 * Whether a node carries whitespace.
	 */
	protected static function hasWhitespace(Node $node): bool
	{
		if (
			$node instanceof HardBreak ||
			$node instanceof SoftBreak
		) {
			return true;
		}

		if ($node instanceof Text) {
			return preg_match('/\s/', $node->text) === 1;
		}

		if ($node instanceof Element && $node->children !== null) {
			foreach ($node->children as $child) {
				if (self::hasWhitespace($child) === true) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Whether the content between an opener
	 * and closer cell contains any whitespace.
	 */
	protected function hasWhitespaceBetween(
		object $opener,
		object $closer
	): bool {
		for (
			$cell = $opener->next;
			$cell !== $closer;
			$cell = $cell->next
		) {
			if (self::hasWhitespace($cell->node) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replaces an unmatched delimiter run with literal text.
	 */
	protected static function materialize(Node $node): Node
	{
		if ($node instanceof Delimiter) {
			return new Text(str_repeat($node->marker, $node->length));
		}

		return $node;
	}

	/**
	 * Opens a bracket delimiter (`[` or `![`).
	 */
	public function open(string $marker, int $content): void
	{
		$cell = $this->append(new Text($marker));

		$cell->bracket    = $marker;
		$cell->active     = true;
		$cell->content    = $content;
		$cell->floor      = $this->last;
		$this->brackets[] = $cell;
	}

	public function opener(): object|null
	{
		if ($this->brackets === []) {
			return null;
		}

		return $this->brackets[array_key_last($this->brackets)];
	}

	protected function unlink(object $cell): void
	{
		$cell->prev->next = $cell->next;

		if ($cell->next !== null) {
			$cell->next->prev = $cell->prev;
		} else {
			$this->tail = $cell->prev;
		}

		$this->unthread($cell);
	}

	protected function unthread(object $cell): void
	{
		if ($cell->pdelim !== null) {
			$cell->pdelim->ndelim = $cell->ndelim;
		} else {
			$this->first = $cell->ndelim;
		}

		if ($cell->ndelim !== null) {
			$cell->ndelim->pdelim = $cell->pdelim;
		} else {
			$this->last = $cell->pdelim;
		}
	}

}
