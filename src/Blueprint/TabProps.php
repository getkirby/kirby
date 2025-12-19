<?php

namespace Kirby\Blueprint;

use Kirby\Toolkit\Str;

class TabProps
{
	public static function fromColumns(string $name, array $columns): array
	{
		return [
			'columns' => $columns,
			'name'    => $name,
		];
	}

	public static function normalize(string $name, array $props): array
	{
		// inject all tab extensions
		$props = Blueprint::extend($props);

		// inject a preset if available
		$props = Blueprint::preset($props);

		$props = FieldsProps::convertToSections($name . '-fields', $props);
		$props = SectionsProps::convertToColumns($props);

		return [
			'columns' => [],
			'icon'    => null,
			'label'   => Str::label($name),
			'name'    => $name,
			...$props,
		];
	}
}
