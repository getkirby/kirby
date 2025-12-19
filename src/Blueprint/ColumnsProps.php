<?php

namespace Kirby\Blueprint;

class ColumnsProps
{
	/**
	 * Converts all column definitions, that
	 * are not wrapped in a tab, into a generic tab
	 */
	public static function convertToTabs(
		string $tab,
		array $props
	): array {
		if (isset($props['columns']) === false) {
			return $props;
		}

		// wrap everything in a main tab
		$props['tabs'] = [
			$tab => TabProps::fromColumns($tab, $props['columns'])
		];

		unset($props['columns']);

		return $props;
	}

	public static function fromSections(array $sections): array
	{
		return [
			ColumnProps::fromSections($sections)
		];
	}

	public static function normalize(string $tab, array $columns): array
	{
		$columns = array_filter($columns, fn ($column) => is_array($column) === true);

		foreach ($columns as $id => $props) {
			$columns[$id] = ColumnProps::normalize($tab, $id, $props);
		}

		return $columns;
	}
}
