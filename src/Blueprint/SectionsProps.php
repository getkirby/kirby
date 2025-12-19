<?php

namespace Kirby\Blueprint;

class SectionsProps
{
	/**
	 * Converts all sections that are not wrapped in
	 * columns, into a single generic column.
	 */
	public static function convertToColumns(array $props): array
	{
		if (isset($props['sections']) === false) {
			return $props;
		}

		// wrap everything in one big column
		$props['columns'] = [
			ColumnProps::fromSections($props['sections'])
		];

		unset($props['sections']);

		return $props;
	}

	public static function fromFields(string $name, array $fields): array
	{
		return [
			$name => SectionProps::fromFields($name, $fields)
		];
	}

	/**
	 * Normalizes all required keys in sections
	 */
	public static function normalize(
		array $sections
	): array {
		// remove sections if the props are false
		$sections = array_filter($sections, fn ($section) => $section !== false);

		foreach ($sections as $sectionName => $sectionProps) {
			$sections[$sectionName] = SectionProps::normalize($sectionName, $sectionProps);
		}

		return $sections;
	}
}
