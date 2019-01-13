<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Sql;

/**
 * Sqlite query builder
 */
class Sqlite extends Sql
{

    /**
     * Returns a list of columns for a specified table
     * SQLite version
     *
     * @param string $table The table name
     * @return string
     */
    public function columns(string $table): array
    {
        return [
            'query'    => 'PRAGMA table_info(' . $this->tableName($table) . ')',
            'bindings' => [],
        ];
    }

    /**
     * Optional key definition for the column.
     *
     * @param array $column
     * @return array
     */
    public function columnKey(array $column): array
    {
        if (isset($column['key']) === false || $column['key'] === 'INDEX') {
            return [
                'query' => null,
                'bindings' => []
            ];
        }

        return [
            'query'    => $column['key'],
            'bindings' => []
        ];
    }

    /**
     * Abstracted column types to simplify table
     * creation for multiple database drivers
     *
     * @return array
     */
    public function columnTypes(): array
    {
        return [
            'id'        => '{{ name }} INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE',
            'varchar'   => '{{ name }} TEXT {{ null }} {{ key }} {{ default }}',
            'text'      => '{{ name }} TEXT {{ null }} {{ key }} {{ default }}',
            'int'       => '{{ name }} INTEGER {{ null }} {{ key }} {{ default }}',
            'timestamp' => '{{ name }} INTEGER {{ null }} {{ key }} {{ default }}'
        ];
    }

    /**
     * Combines an identifier (table and column)
     * SQLite version
     *
     * @param $table string
     * @param $column string
     * @param $values boolean Whether the identifier is going to be used for a values clause
     *                        Only relevant for SQLite
     * @return string
     */
    public function combineIdentifier(string $table, string $column, bool $values = false): string
    {
        // SQLite doesn't support qualified column names for VALUES clauses
        if ($values) {
            return $this->quoteIdentifier($column);
        }

        return $this->quoteIdentifier($table) . '.' . $this->quoteIdentifier($column);
    }

    /**
     * Quotes an identifier (table *or* column)
     *
     * @param $identifier string
     * @return string
     */
    public function quoteIdentifier(string $identifier): string
    {
        // * is special
        if ($identifier === '*') {
            return $identifier;
        }

        // replace every quote with two quotes
        $identifier = str_replace('"', '""', $identifier);

        // wrap in quotes
        return '"' . $identifier . '"';
    }

    /**
     * Returns a list of tables of the database
     * SQLite version
     *
     * @param string $database The database name
     * @return string
     */
    public function tables(): array
    {
        return [
            'query'    => 'SELECT name FROM sqlite_master WHERE type = "table"',
            'bindings' => []
        ];
    }
}
