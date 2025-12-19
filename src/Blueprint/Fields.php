<?php

namespace Kirby\Blueprint;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Helper class to normalize and collect fields from blueprints
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Fields
{
	/**
	 * Creates an error field for fields that use existing names
	 */
	public static function existingFieldError(string $name, string|null $label = null): array
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
	public static function fieldError(string $name, string $message): array
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
	public static function missingFieldsError(string $name): array
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
	public static function normalizeFieldProps(array|string $props): array
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
			$props['fields'] = static::normalizeFieldsProps($props['fields']);
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

	/**
	 * Normalizes all fields and adds automatic labels,
	 * types and widths.
	 */
	public static function normalizeFieldsProps($fields): array
	{
		if (is_array($fields) === false) {
			$fields = [];
		}

		foreach ($fields as $fieldName => $fieldProps) {
			// extend field from string
			if (is_string($fieldProps) === true) {
				$fieldProps = [
					'extends' => $fieldProps,
					'name'    => $fieldName
				];
			}

			// use the name as type definition
			if ($fieldProps === true) {
				$fieldProps = [];
			}

			// unset / remove field if its property is false
			if ($fieldProps === false) {
				unset($fields[$fieldName]);
				continue;
			}

			// inject the name
			$fieldProps['name'] = $fieldName;

			// create all props
			try {
				$fieldProps = static::normalizeFieldProps($fieldProps);
			} catch (Throwable $e) {
				$fieldProps = static::fieldError($fieldName, $e->getMessage());
			}

			// resolve field groups
			if ($fieldProps['type'] === 'group') {
				if (
					empty($fieldProps['fields']) === false &&
					is_array($fieldProps['fields']) === true
				) {
					$index  = array_search($fieldName, array_keys($fields));
					$fields = [
						...array_slice($fields, 0, $index),
						...$fieldProps['fields'] ?? [],
						...array_slice($fields, $index + 1)
					];
				} else {
					unset($fields[$fieldName]);
				}
			} else {
				$fields[$fieldName] = $fieldProps;
			}
		}

		return $fields;
	}
}
