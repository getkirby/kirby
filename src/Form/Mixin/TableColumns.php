<?php

namespace Kirby\Form\Mixin;

trait TableColumns
{
	/**
	 * Columns definition for the table
	 */
	protected array|null $columns = null;

	/**
	 * Cache for the columns definition
	 */
	protected array $columnsCache;

	public function columns(): array
	{
		return $this->columnsCache ??= $this->normalizeColumns(
			columns: $this->columns ?? [],
			fields: $this->fields()
		);
	}

	protected function columnsFromFields(array $fields): array
	{
		// get all field names
		$columnNames = array_column($fields, 'name');

		// create keys for each name
		return array_fill_keys($columnNames, true);
	}

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

		// make the first column visible on mobile
		// if no other mobile columns are defined
		if (in_array(true, array_column($columns, 'mobile'), true) === false) {
			$columns[array_key_first($columns)]['mobile'] = true;
		}

		return $columns;
	}
}
