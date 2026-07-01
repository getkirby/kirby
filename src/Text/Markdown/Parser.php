<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Document;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Grammar;

/**
 * Parses and renders Markdown text to HTML.
 *
 * Behaviour and output are derived from Parsedown and ParsedownExtra by
 * Emanuil Rusev (MIT); see `ATTRIBUTION.md`.
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
	 *     spans: list<class-string<\Kirby\Text\Markdown\Span>>
	 * }
	 */
	public static array $components = [
		'blocks' => [
			Block\AtxHeading::class,
			Block\SetextHeading::class,
			Block\Table::class,
			Block\DefinitionList::class,
			Block\Comment::class,
			Block\Html::class,
			Block\Quote::class,
			Block\Footnotes::class,
			Block\Reference::class,
			Block\FencedCode::class,
			Block\HorizontalRule::class,
			Block\Lists::class,
			Block\Abbreviation::class,
			Block\IndentedCode::class,
		],
		'spans' => [
			Span\Image::class,
			Span\SpecialCharacter::class,
			Span\Emphasis::class,
			Span\Url::class,
			Span\UrlTag::class,
			Span\Email::class,
			Span\Markup::class,
			Span\Footnote::class,
			Span\Link::class,
			Span\Code::class,
			Span\Strikethrough::class,
			Span\EscapedChar::class,
		]
	];

	protected Blocks|null $blocks = null;
	protected Data|null $data = null;
	protected Grammar|null $grammar = null;
	protected Resolver|null $resolver = null;
	protected Spans|null $spans = null;

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
			true  => $this->spans()->parse($text),
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

	public function spans(): Spans
	{
		return $this->spans ??= new Spans($this);
	}
}
