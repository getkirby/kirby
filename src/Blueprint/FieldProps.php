<?php

namespace Kirby\Blueprint;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Toolkit\Str;

class FieldProps
{
	public static function convertGroupToFields(array $fields, string $name, array $group): array
	{
		// the group does not have valid fields
		if (isset($group['fields']) === false || is_array($group['fields']) === false) {
			unset($fields[$name]);
			return $fields;
		}

		// find the position of the group
		$index = array_search($name, array_keys($fields));

		// add the fields of the group at the right position
		$fields = [
			...array_slice($fields, 0, $index),
			...$group['fields'] ?? [],
			...array_slice($fields, $index + 1)
		];

		return $fields;
	}

	/**
	 * Creates an error field for fields that use existing names
	 */
	public static function forExistingFieldError(string $name, string|null $label = null): array
	{
		return [
			'label' => $label ?? 'Error',
			'text'  => 'The field name <strong>"' . $name . '"</strong> already exists in your blueprint.',
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Creates an error field with the given error message
	 */
	public static function forFieldError(string $name, string $message): array
	{
		return [
			'label' => 'Error',
			'name'  => $name,
			'text'  => strip_tags($message),
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Creates an error field for fields sections that don't define any fields
	 */
	public static function forMissingFieldsError(string $name): array
	{
		return [
			'name'  => $name,
			'label' => 'Fields',
			'text'  => 'No fields yet',
			'type'  => 'info'
		];
	}

	/**
	 * Normalize field props for a single field
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the filed name is missing or the field type is invalid
	 */
	public static function normalize(array|string $props): array
	{
		$props = Blueprint::extend($props);

		if (isset($props['name']) === false) {
			throw new InvalidArgumentException(
				message: 'The field name is missing'
			);
		}

		$name = $props['name'];
		$type = $props['type'] ?? $name;

		if ($type !== 'group' && isset(Field::$types[$type]) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid field type ("' . $type . '")'
			);
		}

		// support for nested fields
		if (isset($props['fields']) === true) {
			$props['fields'] = FieldsProps::normalize($props['fields']);
		}

		// groups don't need all the crap
		if ($type === 'group') {
			$fields = $props['fields'];

			if (isset($props['when']) === true) {
				$fields = array_map(
					fn ($field) => array_replace_recursive(['when' => $props['when']], $field),
					$fields
				);
			}

			return [
				'fields' => $fields,
				'name'   => $name,
				'type'   => $type
			];
		}

		// add some useful defaults
		return [
			...$props,
			'label' => $props['label'] ?? Str::label($name),
			'name'  => $name,
			'type'  => $type,
			'width' => $props['width'] ?? '1/1',
		];
	}
}
