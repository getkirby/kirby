<?php

namespace Kirby\Blueprint;

class Sections
{
	/**
	 * Creates the props for an info section to jump in for a
	 * section with an invalid type
	 */
	public static function missingTypeError(string $name, string|null $type = null): array
	{
		return [
			'name'  => $name,
			'label' => $type !== null ? 'Invalid section type ("' . $type . '")' : 'Invalid section type for section "' . $name . '"',
			'text'  => 'The following section types are available: ' . Blueprint::helpList(array_keys(Section::$types)),
			'type'  => 'info'
		];
	}

	/**
	 * Normalizes all required keys in sections
	 */
	public static function normalizeSectionsProps(
		array $sections
	): array {
		foreach ($sections as $sectionName => $sectionProps) {
			// unset / remove section if its property is false
			if ($sectionProps === false) {
				unset($sections[$sectionName]);
				continue;
			}

			// fallback to default props when true is passed
			if ($sectionProps === true) {
				$sectionProps = [];
			}

			// inject all section extensions
			$sectionProps = Blueprint::extend($sectionProps);

			$sections[$sectionName] = $sectionProps = [
				...$sectionProps,
				'name' => $sectionName,
				'type' => $type = $sectionProps['type'] ?? $sectionName
			];

			if (empty($type) === true || is_string($type) === false) {
				$sections[$sectionName] = static::missingTypeError($sectionName);
			} elseif (isset(Section::$types[$type]) === false) {
				$sections[$sectionName] = static::missingTypeError($sectionName, $type);
			}
		}

		return $sections;
	}
}
