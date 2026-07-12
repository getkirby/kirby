<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * Asserts that the given marker character dispatches to the
	 * given block or span component.
	 *
	 * @param class-string<Block|Inline> $component
	 */
	public function assertMarkerDispatchesComponent(
		string $component,
		string $marker
	): void {
		$grammar = (new Parser())->grammar();

		$candidates = is_subclass_of($component, Block::class) === true
			? $grammar->blocks($marker)
			: $grammar->inlines($marker);

		$classes = array_map(
			fn ($candidate) => $candidate::class,
			$candidates
		);

		$this->assertContains(
			$component,
			$classes,
			'Failed asserting that marker "' . $marker . '" dispatches to ' . $component
		);
	}

	/**
	 * Asserts that every marker the component declares dispatches
	 * back to it. Driven by `::markers()`.
	 *
	 * @param class-string<Block|Inline> $component
	 */
	public function assertMarkersDispatchComponent(string $component): void
	{
		foreach ($component::markers() as $marker) {
			$this->assertMarkerDispatchesComponent($component, $marker);
		}
	}
}
