<?php

namespace Kirby\Blueprint;

use Throwable;

class FieldsProps
{
	/**
	 * Converts all field definitions, that are not
	 * wrapped in a fields section into a generic
	 * fields section.
	 */
	public static function convertToSections(
		string $sectionName,
		array $props
	): array {
		if (isset($props['fields']) === false) {
			return $props;
		}

		// wrap all fields in a section
		$props['sections'] = [
			$sectionName => SectionProps::fromFields($sectionName, $props['fields'])
		];

		unset($props['fields']);

		return $props;
	}

	/**
	 * Normalizes all fields and adds automatic labels,
	 * types and widths.
	 */
	public static function normalize($fields): array
	{
		if (is_array($fields) === false) {
			$fields = [];
		}

		// remove fields if the props are false
		$fields = array_filter($fields, fn ($field) => $field !== false);

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

			// inject the name
			$fieldProps['name'] = $fieldName;

			// create all props
			try {
				$fieldProps = FieldProps::normalize($fieldProps);
			} catch (Throwable $e) {
				$fieldProps = FieldProps::forFieldError($fieldName, $e->getMessage());
			}

			// resolve field groups
			if ($fieldProps['type'] === 'group') {
				$fields = FieldProps::convertGroupToFields($fields, $fieldName, $fieldProps);
			} else {
				$fields[$fieldName] = $fieldProps;
			}
		}

		return $fields;
	}
}
