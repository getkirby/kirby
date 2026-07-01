<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Span;

/**
 * The Markdown grammar represents the block
 * and span components that make up the parser.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Grammar
{
	/**
	 * @var array<class-string, \Kirby\Text\Markdown\Block|\Kirby\Text\Markdown\Span>
	 */
	protected array $components = [];

	/**
	 * @var array<string, list<\Kirby\Text\Markdown\Block>>
	 */
	protected array $blocks = [];

	/**
	 * @var array<string, list<class-string<\Kirby\Text\Markdown\Block>>>
	 */
	protected array $blockMarkers = [];

	/**
	 * The components that transform the resolved document,
	 * in registration order; resolved once on first use.
	 *
	 * @var list<Transform>|null
	 */
	protected array|null $transforms = null;

	/**
	 * All span markers as a string
	 */
	protected string $markers = '';

	/**
	 * @var array<string, list<\Kirby\Text\Markdown\Span>>
	 */
	protected array $spans = [];

	/**
	 * @var array<string, list<class-string<\Kirby\Text\Markdown\Span>>>
	 */
	protected array $spanMarkers = [];

	public function __construct(
		protected Parser $parser
	) {
		// build the marker dispatch maps from the live component
		// registry, so plugin-registered blocks and spans are picked up
		foreach (Parser::$components['blocks'] as $class) {
			foreach ($class::markers() as $marker) {
				$this->blockMarkers[$marker][] = $class;
			}
		}

		foreach (Parser::$components['spans'] as $class) {
			foreach ($class::markers() as $marker) {
				$this->spanMarkers[$marker][] = $class;
			}
		}

		$this->markers = implode('', array_keys($this->spanMarkers));
	}

	public function block(string $class): Block|null
	{
		return $this->get(Parser::$components['blocks'], $class);
	}

	/**
	 * @return list<Block>
	 */
	public function blocks(string $marker): array
	{
		return $this->blocks[$marker] ??= $this->instances(
			$this->blockMarkers[$marker] ?? []
		);
	}

	/**
	 * @template T of \Kirby\Text\Markdown\Block|\Kirby\Text\Markdown\Span
	 * @param list<class-string<T>> $classes
	 * @return T|null
	 */
	protected function get(array $classes, string $sought): Block|Span|null
	{
		if (isset($this->components[$sought]) === true) {
			/** @var T $component */
			$component = $this->components[$sought];
			return $component;
		}

		foreach ($classes as $class) {
			if (is_a($class, $sought, true) === true) {
				/** @var T $component */
				$component = $this->components[$class] ??= new $class($this->parser);
				return $component;
			}
		}

		return null;
	}

	/**
	 * The registered components that transform the fully resolved
	 * document, e.g. appending the footnotes section or merging
	 * adjacent definition lists.
	 *
	 * @return list<Transform>
	 */
	public function transforms(): array
	{
		if ($this->transforms !== null) {
			return $this->transforms;
		}

		$transforms = [];
		$classes    = [
			...Parser::$components['blocks'],
			...Parser::$components['spans']
		];

		foreach ($this->instances($classes) as $component) {
			if ($component instanceof Transform) {
				$transforms[] = $component;
			}
		}

		return $this->transforms = $transforms;
	}

	/**
	 * Resolves a list of component class names to
	 * their shared instances, creating each one on first use.
	 *
	 * @template T of Block|Span
	 * @param iterable<class-string<T>> $classes
	 * @return list<T>
	 */
	protected function instances(iterable $classes): array
	{
		$instances = [];

		foreach ($classes as $class) {
			/** @var T $instance */
			$instance    = $this->components[$class] ??= new $class($this->parser);
			$instances[] = $instance;
		}

		return $instances;
	}

	/**
	 * All span markers characters as a string for `strpbrk()`.
	 */
	public function markers(): string
	{
		return $this->markers;
	}

	public function span(string $class): Span|null
	{
		return $this->get(Parser::$components['spans'], $class);
	}

	/**
	 * @return list<\Kirby\Text\Markdown\Span>
	 */
	public function spans(string $marker): array
	{
		return $this->spans[$marker] ??= $this->instances(
			$this->spanMarkers[$marker] ?? []
		);
	}
}
