<?php

namespace Kirby\Blueprint;

class SectionProps
{
	public static function forMissingSectionsError(string $name, string|null $width = null): array
	{
		return [
			'label' => 'Column (' . ($width ?? '1/1') . ')',
			'name'  => $name,
			'text'  => 'No sections yet',
			'type'  => 'info',
		];
	}

	/**
	 * Creates the props for an info section to jump in for a
	 * section with an invalid type
	 */
	public static function forMissingTypeError(string $name, string|null $type = null): array
	{
		return [
			'label' => $type !== null ? 'Invalid section type ("' . $type . '")' : 'Invalid section type for section "' . $name . '"',
			'name'  => $name,
			'text'  => 'The following section types are available: ' . Blueprint::helpList(array_keys(Section::$types)),
			'type'  => 'info'
		];
	}

	public static function fromFields(string $name, array $fields): array
	{
		return [
			'fields' => $fields,
			'name'   => $name,
			'type'   => 'fields',
		];
	}

	/**
	 * Normalizes all required keys in sections
	 */
	public static function normalize(
		string $name,
		array|bool|string $props
	): array {
		// fallback to default props when true is passed
		if ($props === true) {
			$props = [];
		}

		// inject all section extensions
		$props = Blueprint::extend($props);

		// add required fields
		$props = [
			...$props,
			'name' => $name,
			'type' => $type = $props['type'] ?? $name
		];

		if (empty($type) === true || is_string($type) === false) {
			return static::forMissingTypeError($name);
		}

		if (isset(Section::$types[$type]) === false) {
			return static::forMissingTypeError($name, $type);
		}

		if ($type === 'fields') {
			$fields = FieldsProps::normalize($props['fields'] ?? []);

			// inject guide fields guide
			if ($fields === []) {
				$fields = [
					$name . '-info' => FieldProps::forMissingFieldsError($name . '-info')
				];
			}

			$props['fields'] = $fields;
		}

		return $props;
	}
}
