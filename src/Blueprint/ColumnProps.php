<?php

namespace Kirby\Blueprint;

class ColumnProps
{
	/**
	 * Wrap sections in one full-width column
	 */
	public static function fromSections(array $sections): array
	{
		return [
			'width'    => '1/1',
			'sections' => $sections
		];
	}

	public static function normalize(string $tab, string|int $id, array $props): array
	{
		$props = FieldsProps::convertToSections(
			$tab . '-col-' . $id . '-fields',
			$props
		);

		// add column defaults
		$props = [
			'width'    => '1/1',
			'sections' => [],
			...$props
		];

		// inject getting started info, if the sections are empty
		if ($props['sections'] === []) {
			$sectionName = $tab . '-info-' . $id;

			$props['sections'] = [
				$sectionName => SectionProps::forMissingSectionsError($sectionName, $props['width'])
			];
		}

		$props['sections'] = SectionsProps::normalize($props['sections']);

		return $props;
	}
}
