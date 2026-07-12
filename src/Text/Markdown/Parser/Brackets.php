<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Inline\Image;
use Kirby\Text\Markdown\Inline\Link;
use Kirby\Text\Markdown\Parser;

/**
 * Resolves brackets (`[…]`, `![…]`) while scanning inlines.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Brackets
{
	protected Image|null $image = null;
	protected Link|null $link = null;

	public function __construct(
		protected Parser $parser,
		protected Text $text
	) {
	}

	/**
	 * Resolves a `]`:
	 * pushes the text before it, then looks back
	 * for an open bracket and, when it is active
	 * and a destination or reference follows,
	 * forms a link or image.
	 */
	public function close(Phrase $phrase, Stack $stack): void
	{
		$phrase->take(1);
		$stack->add($this->text->build($phrase->lead()));

		$opener = $stack->opener();

		// no opener, or one a nested link already disabled: literal `]`
		if ($opener === null || $opener->active === false) {
			if ($opener !== null) {
				$stack->drop();
			}

			$phrase->flush();
			$stack->add($this->text->build(']'));
			return;
		}

		// the raw source between the brackets
		$label = $phrase->source(
			$opener->content,
			$phrase->offset() - $opener->content
		);

		$resolved = $this->link()->open($phrase->after(), $label);

		// no destination or matching reference: literal `]`
		if ($resolved === null) {
			$stack->drop();
			$phrase->flush();
			$stack->add($this->text->build(']'));
			return;
		}

		// the opener's bracket decides
		// which component renders it;
		// each builds its own element
		$component = $opener->bracket === '!['
			? $this->image()
			: $this->link();

		$phrase->extend($resolved['length']);
		$phrase->flush();

		$stack->close(
			fn (array $children): Element =>
				$component->element($resolved, $children)
		);
	}

	protected function image(): Image
	{
		/** @var \Kirby\Text\Markdown\Inline\Image */
		return $this->image ??= $this->parser->grammar()->inline(Image::class);
	}

	protected function link(): Link
	{
		/** @var \Kirby\Text\Markdown\Inline\Link */
		return $this->link ??= $this->parser->grammar()->inline(Link::class);
	}

	/**
	 * Opens a link/image bracket (`[` or `![`):
	 * emits the text before it and records the opener
	 * on the stack, noting where its raw label begins.
	 */
	public function open(Phrase $phrase, Stack $stack, string $marker): void
	{
		$phrase->take(strlen($marker));
		$text = $this->text->build($phrase->lead());
		$stack->add($text);
		$stack->open($marker, $phrase->offset() + strlen($marker));
		$phrase->flush();
	}
}
