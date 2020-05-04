<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Sql;

/**
 * SQLite query builder
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Sqlite extends Sql
{
    /**
     * Returns a query to list the columns of a specified table;
     * the query needs to return rows with a column `name`
     *
     * @param string $table Table name
     * @return array
     */
    public function columns(string $table): array
    {
        return [
            'query'    => 'PRAGMA table_info(' . $this->tableName($table) . ')',
            'bindings' => [],
        ];
    }

    /**
     * Abstracted column types to simplify table
     * creation for multiple database drivers
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function columnTypes(): array
    {
        return [
            'id'        => '{{ name }} INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE',
            'varchar'   => '{{ name }} TEXT {{ null }} {{ default }} {{ unique }}',
            'text'      => '{{ name }} TEXT {{ null }} {{ default }} {{ unique }}',
            'int'       => '{{ name }} INTEGER {{ null }} {{ default }} {{ unique }}',
            'timestamp' => '{{ name }} INTEGER {{ null }} {{ default }} {{ unique }}'
        ];
    }

    /**
     * Combines an identifier (table and column)
     *
     * @param $table string
     * @param $column string
     * @param $values boolean Whether the identifier is going to be used for a VALUES clause;
     *                        only relevant for SQLite
     * @return string
     */
    public function combineIdentifier(string $table, string $column, bool $values = false): string
    {
        // SQLite doesn't support qualified column names for VALUES clauses
        if ($values === true) {
            return $this->quoteIdentifier($column);
        }

        return $this->quoteIdentifier($table) . '.' . $this->quoteIdentifier($column);
    }

    /**
     * Creates a CREATE TABLE query
     *
     * @param string $table Table name
     * @param array $columns Array of column definition arrays, see `Kirby\Database\Sql::createColumn()`
     * @return array Array with a `query` string and a `bindings` array
     */
    public function createTable(string $table, array $columns = []): array
    {
        $inner = $this->createTableInner($columns);

        // add keys
        $keys = [];
        foreach ($inner['keys'] as $key => $columns) {
            // quote each column name and make a list string out of the column names
            $columns = implode(', ', array_map(function ($name) {
                return $this->quoteIdentifier($name);
            }, $columns));

            if ($key === 'primary') {
                $inner['query'] .= ',' . PHP_EOL . 'PRIMARY KEY (' . $columns . ')';
            } else {
                // SQLite only supports index creation using a separate CREATE INDEX query
                $unique = isset($inner['unique'][$key]) === true ? 'UNIQUE ' : '';
                $keys[] = 'CREATE ' . $unique . 'INDEX ' . $this->quoteIdentifier($table . '_index_' . $key) .
                             ' ON ' . $this->quoteIdentifier($table) . ' (' . $columns . ')';
            }
        }

        $query = 'CREATE TABLE ' . $this->quoteIdentifier($table) . ' (' . PHP_EOL . $inner['query'] . PHP_EOL . ')';
        if (empty($keys) === false) {
            $query .= ';' . PHP_EOL . implode(';' . PHP_EOL, $keys);
        }

        return [
            'query'    => $query,
            'bindings' => $inner['bindings']
        ];
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

        // escape quotes inside the identifier name
        $identifier = str_replace('"', '""', $identifier);

        // wrap in quotes
        return '"' . $identifier . '"';
    }

    /**
     * Returns a query to list the tables of the current database;
     * the query needs to return rows with a column `name`
     *
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
