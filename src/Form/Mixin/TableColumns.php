<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `columns` prop for table layout column definitions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait TableColumns
{
	/**
	 * Columns definition for the table
	 */
	#[Prop(computed: true)]
	protected array|null $columns = null;

	/**
	 * Cache for the columns definition
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	protected array|null $columnsCache = null;

	/**
	 * Returns the normalized columns, keyed by field name
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function columns(): array
	{
		return $this->columnsCache ??= $this->normalizeColumns(
			columns: $this->columns ?? [],
			fields: $this->fields()
		);
	}

	/**
	 * @param array<string, array<string, mixed>> $fields
	 * @return array<string, true>
	 */
	protected function columnsFromFields(array $fields): array
	{
		$names = array_column($fields, 'name');
		return array_fill_keys($names, true);
	}

	/**
	 * @param array<string, mixed>|true $column
	 * @param array<string, mixed>|null $field
	 * @return array<string, mixed>|null
	 */
	protected function normalizeColumn(string $name, array|true $column, array|null $field): array|null
	{
		// Skip empty and unsaveable fields
		// They should never be included as column
		if ($field === null || $field['saveable'] === false || $field['type'] === 'hidden' || $field['hidden'] === true) {
			return null;
		}

		if (is_array($column) === false) {
			$column = [];
		}

		$column['type']  ??= $field['type'];
		$column['label'] ??= $field['label'] ?? $name;
		$column['label']   = $this->i18n($column['label']);

		return $column;
	}

	/**
	 * @param array<string, array<string, mixed>> $fields
	 */
	protected function normalizeColumns(array $columns, array $fields): array
	{
		// lower case all keys, because field names will
		// be lowercase as well.
		$columns = array_change_key_case($columns);

		// create auto-columns from fields
		if ($columns === []) {
			$columns = $this->columnsFromFields($fields);
		}

		foreach ($columns as $name => $column) {
			$columns[$name] = $this->normalizeColumn($name, $column, $fields[$name] ?? null);
		}

		$columns = array_filter($columns);

		if ($columns === []) {
			return [];
		}

		// make the first column visible on mobile
		// if no other mobile columns are defined
		if (in_array(true, array_column($columns, 'mobile'), true) === false) {
			$columns[array_key_first($columns)]['mobile'] = true;
		}

		return $columns;
	}
}
