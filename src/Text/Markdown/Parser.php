<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Document;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Grammar;

/**
 * Parses and renders Markdown text to HTML.
 *
 * Strives to implement the CommonMark 0.31.2 specification,
 * extended with the ParsedownExtra features Kirby supports.
 * Inspired by the ground work or Parsedown and Parsedown Extra.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Parser
{
	/**
	 * @var array{
	 *     blocks: list<class-string<\Kirby\Text\Markdown\Block>>,
	 *     inlines: list<class-string<\Kirby\Text\Markdown\Inline>>
	 * }
	 */
	public static array $components = [
		'blocks' => [
			Block\AtxHeading::class,
			Block\SetextHeading::class,
			Block\Table::class,
			Block\DefinitionList::class,
			Block\Html::class,
			Block\BlockQuote::class,
			Block\Footnotes::class,
			Block\LinkDefinition::class,
			Block\FencedCode::class,
			Block\ThematicBreak::class,
			Block\Lists::class,
			Block\Abbreviation::class,
			Block\IndentedCode::class,
		],
		'inlines' => [
			Inline\Image::class,
			Inline\CharacterReference::class,
			Inline\Emphasis::class,
			Inline\Underscore::class,
			Inline\Url::class,
			Inline\Autolink::class,
			Inline\Email::class,
			Inline\RawHtml::class,
			Inline\Footnote::class,
			Inline\Link::class,
			Inline\CodeSpan::class,
			Inline\Strikethrough::class,
			Inline\BackslashEscape::class,
		]
	];

	protected Blocks|null $blocks = null;
	protected Data|null $data = null;
	protected Grammar|null $grammar = null;
	protected Resolver|null $resolver = null;
	protected Inlines|null $inlines = null;

	public function __construct(
		public readonly bool $breaks = false,
		public readonly bool $safe = false
	) {
	}

	public function blocks(): Blocks
	{
		return $this->blocks ??= new Blocks($this);
	}

	public function data(): Data
	{
		return $this->data ??= new Data();
	}

	public function grammar(): Grammar
	{
		return $this->grammar ??= new Grammar($this);
	}

	public function inlines(): Inlines
	{
		return $this->inlines ??= new Inlines($this);
	}

	/**
	 * Parses Markdown to HTML
	 */
	public function parse(
		string|null $text,
		bool $inline = false
	): string {
		// standardize line breaks once at the entry point
		$text = str_replace(["\r\n", "\r"], "\n", $text ?? '');

		$this->data()->reset();

		$nodes = match ($inline) {
			true  => $this->inlines()->parse($text),
			false => $this->blocks()->parse($text)
		};

		// resolve each element's deferred content into the final AST
		$nodes = $this->resolver()->nodes($nodes);

		if ($inline === false) {
			foreach ($this->grammar()->transforms() as $transform) {
				$nodes = $transform->transform($nodes);
			}
		}

		// render the tree to HTML
		$doc  = new Document($nodes);
		$html = $this->render($doc);

		return $inline === true ? $html : trim($html, "\n");
	}

	protected function render(Node $node): string
	{
		return (new Renderer($this->safe))->render($node);
	}

	public function resolver(): Resolver
	{
		return $this->resolver ??= new Resolver($this);
	}
}
