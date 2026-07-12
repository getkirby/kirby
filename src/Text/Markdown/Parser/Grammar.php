<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser;

/**
 * The Markdown grammar represents the block
 * and inline components that make up the parser.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Grammar
{
	/**
	 * @var array<class-string, \Kirby\Text\Markdown\Block|\Kirby\Text\Markdown\Inline>
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
	 * @var array<string, list<\Kirby\Text\Markdown\Inline>>
	 */
	protected array $inlines = [];

	/**
	 * @var array<string, list<class-string<\Kirby\Text\Markdown\Inline>>>
	 */
	protected array $inlineMarkers = [];

	public function __construct(
		protected Parser $parser
	) {
		// build the marker dispatch maps from the live component
		// registry, so plugin-registered blocks and inlines are picked up
		foreach (Parser::$components['blocks'] as $class) {
			foreach ($class::markers() as $marker) {
				$this->blockMarkers[$marker][] = $class;
			}
		}

		foreach (Parser::$components['inlines'] as $class) {
			foreach ($class::markers() as $marker) {
				$this->inlineMarkers[$marker][] = $class;
			}
		}
	}

	/**
	 * @template T of \Kirby\Text\Markdown\Block
	 * @param class-string<T> $class
	 * @return T|null
	 */
	public function block(string $class): Block|null
	{
		/** @var T|null */
		return $this->get(Parser::$components['blocks'], $class);
	}

	/**
	 * @return list<\Kirby\Text\Markdown\Block>
	 */
	public function blocks(string $marker): array
	{
		return $this->blocks[$marker] ??= $this->instances(
			$this->blockMarkers[$marker] ?? []
		);
	}

	/**
	 * @param list<class-string> $classes
	 * @param class-string $sought
	 */
	protected function get(array $classes, string $sought): Block|Inline|null
	{
		if (isset($this->components[$sought]) === true) {
			return $this->components[$sought];
		}

		foreach ($classes as $class) {
			if (is_a($class, $sought, true) === true) {
				return $this->components[$class] ??= new $class($this->parser);
			}
		}

		return null;
	}

	/**
	 * @template T of \Kirby\Text\Markdown\Inline
	 * @param class-string<T> $class
	 * @return T|null
	 */
	public function inline(string $class): Inline|null
	{
		/** @var T|null */
		return $this->get(Parser::$components['inlines'], $class);
	}

	/**
	 * @return list<\Kirby\Text\Markdown\Inline>
	 */
	public function inlines(string $marker): array
	{
		return $this->inlines[$marker] ??= $this->instances(
			$this->inlineMarkers[$marker] ?? []
		);
	}

	/**
	 * Resolves a list of component class names to
	 * their shared instances, creating each one on first use.
	 *
	 * @template T of Block|Inline
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
	 * All inline marker characters as a string for `strpbrk()`.
	 */
	public function markers(): string
	{
		return implode('', array_keys($this->inlineMarkers));
	}

	/**
	 * The registered components that transform the fully resolved
	 * document, e.g. appending the footnotes section or merging
	 * adjacent definition lists.
	 *
	 * @return list<\Kirby\Text\Markdown\Parser\Transform>
	 */
	public function transforms(): array
	{
		if ($this->transforms !== null) {
			return $this->transforms;
		}

		$transforms = [];
		$classes    = [
			...Parser::$components['blocks'],
			...Parser::$components['inlines']
		];

		foreach ($this->instances($classes) as $component) {
			if ($component instanceof Transform) {
				$transforms[] = $component;
			}
		}

		return $this->transforms = $transforms;
	}
}
