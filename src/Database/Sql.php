<?php

namespace Kirby\Database;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * SQL Query builder
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Sql
{
	/**
	 * List of literals which should not be escaped in queries
	 */
	public static array $literals = ['NOW()', null];

	/**
	 * The parent database connection
	 */
	protected Database $database;

	/**
	 * List of used bindings; used to avoid
	 * duplicate binding names
	 */
	protected array $bindings = [];

	/**
	 * Constructor
	 * @codeCoverageIgnore
	 */
	public function __construct(Database $database)
	{
		$this->database = $database;
	}

	/**
	 * Returns a randomly generated binding name
	 *
	 * @param string $label String that only contains alphanumeric chars and
	 *                      underscores to use as a human-readable identifier
	 * @return string Binding name that is guaranteed to be unique for this connection
	 */
	public function bindingName(string $label): string
	{
		// make sure that the binding name is safe to prevent injections;
		// otherwise use a generic label
		if (!$label || preg_match('/^[a-zA-Z0-9_]+$/', $label) !== 1) {
			$label = 'invalid';
		}

		// generate random bindings until the name is unique
		do {
			$binding = ':' . $label . '_' . Str::random(8, 'alphaNum');
		} while (in_array($binding, $this->bindings) === true);

		// cache the generated binding name for future invocations
		$this->bindings[] = $binding;
		return $binding;
	}

	/**
	 * Returns a query to list the columns of a specified table;
	 * the query needs to return rows with a column `name`
	 *
	 * @param string $table Table name
	 */
	abstract public function columns(string $table): array;

	/**
	 * Returns a query snippet for a column default value
	 *
	 * @param string $name Column name
	 * @param array $column Column definition array with an optional `default` key
	 * @return array Array with a `query` string and a `bindings` array
	 */
	public function columnDefault(string $name, array $column): array
	{
		if (isset($column['default']) === false) {
			return [
				'query'    => null,
				'bindings' => []
			];
		}

		$binding = $this->bindingName($name . '_default');

		return [
			'query'    => 'DEFAULT ' . $binding,
			'bindings' => [
				$binding => $column['default']
			]
		];
	}

	/**
	 * Returns the cleaned identifier based on the table and column name
	 *
	 * @param string $table Table name
	 * @param string $column Column name
	 * @param bool $enforceQualified If true, a qualified identifier is returned in all cases
	 * @return string|null Identifier or null if the table or column is invalid
	 */
	public function columnName(string $table, string $column, bool $enforceQualified = false): string|null
	{
		// ensure we have clean $table and $column values without qualified identifiers
		list($table, $column) = $this->splitIdentifier($table, $column);

		// combine the identifiers again
		if ($this->database->validateColumn($table, $column) === true) {
			return $this->combineIdentifier($table, $column, $enforceQualified !== true);
		}

		// the table or column does not exist
		return null;
	}

	/**
	 * Abstracted column types to simplify table
	 * creation for multiple database drivers
	 * @codeCoverageIgnore
	 */
	public function columnTypes(): array
	{
		return [
			'id'        => '{{ name }} INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
			'varchar'   => '{{ name }} varchar(255) {{ null }} {{ default }} {{ unique }}',
			'text'      => '{{ name }} TEXT {{ unique }}',
			'int'       => '{{ name }} INT(11) UNSIGNED {{ null }} {{ default }} {{ unique }}',
			'timestamp' => '{{ name }} TIMESTAMP {{ null }} {{ default }} {{ unique }}',
			'bool'      => '{{ name }} TINYINT(1) {{ null }} {{ default }} {{ unique }}'
		];
	}

	/**
	 * Combines an identifier (table and column)
	 *
	 * @param $values bool Whether the identifier is going to be used for a VALUES clause;
	 *                only relevant for SQLite
	 */
	public function combineIdentifier(string $table, string $column, bool $values = false): string
	{
		return $this->quoteIdentifier($table) . '.' . $this->quoteIdentifier($column);
	}

	/**
	 * Creates the CREATE TABLE syntax for a single column
	 *
	 * @param string $name Column name
	 * @param array $column Column definition array; valid keys:
	 *                      - `type` (required): Column template to use
	 *                      - `null`: Whether the column may be NULL (boolean)
	 *                      - `key`: Index this column is part of; special values `'primary'` for PRIMARY KEY and `true` for automatic naming
	 *                      - `unique`: Whether the index (or if not set the column itself) has a UNIQUE constraint
	 *                      - `default`: Default value of this column
	 * @return array Array with `query` and `key` strings, a `unique` boolean and a `bindings` array
	 * @throws \Kirby\Exception\InvalidArgumentException if no column type is given or the column type is not supported.
	 */
	public function createColumn(string $name, array $column): array
	{
		// column type
		if (isset($column['type']) === false) {
			throw new InvalidArgumentException('No column type given for column ' . $name);
		}
		$template = $this->columnTypes()[$column['type']] ?? null;
		if (!$template) {
			throw new InvalidArgumentException('Unsupported column type: ' . $column['type']);
		}

		// null option
		if (A::get($column, 'null') === false) {
			$null = 'NOT NULL';
		} else {
			$null = 'NULL';
		}

		// indexes/keys
		if (isset($column['key']) === true) {
			if (is_string($column['key']) === true) {
				$column['key'] = strtolower($column['key']);
			} elseif ($column['key'] === true) {
				$column['key'] = $name . '_index';
			}
		}

		// unique
		$uniqueKey = false;
		$uniqueColumn = null;
		if (isset($column['unique']) === true && $column['unique'] === true) {
			if (isset($column['key']) === true) {
				// this column is part of an index, make that unique
				$uniqueKey = true;
			} else {
				// make the column itself unique
				$uniqueColumn = 'UNIQUE';
			}
		}

		// default value
		$columnDefault = $this->columnDefault($name, $column);

		$query = trim(Str::template($template, [
			'name'    => $this->quoteIdentifier($name),
			'null'    => $null,
			'default' => $columnDefault['query'],
			'unique'  => $uniqueColumn
		], ['fallback' => '']));

		return [
			'query'    => $query,
			'bindings' => $columnDefault['bindings'],
			'key'      => $column['key'] ?? null,
			'unique'   => $uniqueKey
		];
	}

	/**
	 * Creates the inner query for the columns in a CREATE TABLE query
	 *
	 * @param array $columns Array of column definition arrays, see `Kirby\Database\Sql::createColumn()`
	 * @return array Array with a `query` string and `bindings`, `keys` and `unique` arrays
	 */
	public function createTableInner(array $columns): array
	{
		$query    = [];
		$bindings = [];
		$keys     = [];
		$unique   = [];

		foreach ($columns as $name => $column) {
			$sql = $this->createColumn($name, $column);

			// collect query and bindings
			$query[] = $sql['query'];
			$bindings += $sql['bindings'];

			// make a list of keys per key name
			if ($sql['key'] !== null) {
				if (isset($keys[$sql['key']]) !== true) {
					$keys[$sql['key']] = [];
				}

				$keys[$sql['key']][] = $name;
				if ($sql['unique'] === true) {
					$unique[$sql['key']] = true;
				}
			}
		}

		return [
			'query'    => implode(',' . PHP_EOL, $query),
			'bindings' => $bindings,
			'keys'     => $keys,
			'unique'   => $unique
		];
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
		foreach ($inner['keys'] as $key => $columns) {
			// quote each column name and make a list string out of the column names
			$columns = implode(', ', array_map(
				fn ($name) => $this->quoteIdentifier($name),
				$columns
			));

			if ($key === 'primary') {
				$key = 'PRIMARY KEY';
			} else {
				$unique = isset($inner['unique'][$key]) === true ? 'UNIQUE ' : '';
				$key = $unique . 'INDEX ' . $this->quoteIdentifier($key);
			}

			$inner['query'] .= ',' . PHP_EOL . $key . ' (' . $columns . ')';
		}

		return [
			'query'    => 'CREATE TABLE ' . $this->quoteIdentifier($table) . ' (' . PHP_EOL . $inner['query'] . PHP_EOL . ')',
			'bindings' => $inner['bindings']
		];
	}

	/**
	 * Builds a DELETE clause
	 *
	 * @param array $params List of parameters for the DELETE clause. See defaults for more info.
	 */
	public function delete(array $params = []): array
	{
		$defaults = [
			'table'    => '',
			'where'    => null,
			'bindings' => []
		];

		$options  = array_merge($defaults, $params);
		$bindings = $options['bindings'];
		$query    = ['DELETE'];

		// from
		$this->extend($query, $bindings, $this->from($options['table']));

		// where
		$this->extend($query, $bindings, $this->where($options['where']));

		return [
			'query'    => $this->query($query),
			'bindings' => $bindings
		];
	}

	/**
	 * Creates the sql for dropping a single table
	 */
	public function dropTable(string $table): array
	{
		return [
			'query'    => 'DROP TABLE ' . $this->tableName($table),
			'bindings' => []
		];
	}

	/**
	 * Extends a given query and bindings
	 * by reference
	 */
	public function extend(array &$query, array &$bindings, array $input): void
	{
		if (empty($input['query']) === false) {
			$query[]  = $input['query'];
			$bindings = array_merge($bindings, $input['bindings']);
		}
	}

	/**
	 * Creates the from syntax
	 */
	public function from(string $table): array
	{
		return [
			'query'    => 'FROM ' . $this->tableName($table),
			'bindings' => []
		];
	}

	/**
	 * Creates the group by syntax
	 */
	public function group(string|null $group = null): array
	{
		if (empty($group) === false) {
			$query = 'GROUP BY ' . $group;
		}

		return [
			'query'    => $query ?? null,
			'bindings' => []
		];
	}

	/**
	 * Creates the having syntax
	 */
	public function having(string|null $having = null): array
	{
		if (empty($having) === false) {
			$query = 'HAVING ' . $having;
		}

		return [
			'query'    => $query ?? null,
			'bindings' => []
		];
	}

	/**
	 * Creates an insert query
	 */
	public function insert(array $params = []): array
	{
		$table    = $params['table']  ?? null;
		$values   = $params['values'] ?? null;
		$bindings = $params['bindings'];
		$query    = ['INSERT INTO ' . $this->tableName($table)];

		// add the values
		$this->extend($query, $bindings, $this->values($table, $values, ', ', false));

		return [
			'query'    => $this->query($query),
			'bindings' => $bindings
		];
	}

	/**
	 * Creates a join query
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if an invalid join type is given
	 */
	public function join(string $type, string $table, string $on): array
	{
		$types = [
			'JOIN',
			'INNER JOIN',
			'OUTER JOIN',
			'LEFT OUTER JOIN',
			'LEFT JOIN',
			'RIGHT OUTER JOIN',
			'RIGHT JOIN',
			'FULL OUTER JOIN',
			'FULL JOIN',
			'NATURAL JOIN',
			'CROSS JOIN',
			'SELF JOIN'
		];

		$type = strtoupper(trim($type));

		// validate join type
		if (in_array($type, $types) === false) {
			throw new InvalidArgumentException('Invalid join type ' . $type);
		}

		return [
			'query'    => $type . ' ' . $this->tableName($table) . ' ON ' . $on,
			'bindings' => [],
		];
	}

	/**
	 * Create the syntax for multiple joins
	 */
	public function joins(array|null $joins = null): array
	{
		$query    = [];
		$bindings = [];

		foreach ((array)$joins as $join) {
			$this->extend($query, $bindings, $this->join($join['type'] ?? 'JOIN', $join['table'] ?? null, $join['on'] ?? null));
		}

		return [
			'query'    => implode(' ', array_filter($query)),
			'bindings' => [],
		];
	}

	/**
	 * Creates a limit and offset query instruction
	 */
	public function limit(int $offset = 0, int|null $limit = null): array
	{
		// no need to add it to the query
		if ($offset === 0 && $limit === null) {
			return [
				'query'    => null,
				'bindings' => []
			];
		}

		$limit ??= '18446744073709551615';

		$offsetBinding = $this->bindingName('offset');
		$limitBinding  = $this->bindingName('limit');

		return [
			'query' => 'LIMIT ' . $offsetBinding . ', ' . $limitBinding,
			'bindings' => [
				$limitBinding  => $limit,
				$offsetBinding => $offset,
			]
		];
	}

	/**
	 * Creates the order by syntax
	 */
	public function order(string|null $order = null): array
	{
		if (empty($order) === false) {
			$query = 'ORDER BY ' . $order;
		}

		return [
			'query'    => $query ?? null,
			'bindings' => []
		];
	}

	/**
	 * Converts a query array into a final string
	 */
	public function query(array $query, string $separator = ' '): string
	{
		return implode($separator, array_filter($query));
	}

	/**
	 * Quotes an identifier (table *or* column)
	 */
	public function quoteIdentifier(string $identifier): string
	{
		// * is special, don't quote that
		if ($identifier === '*') {
			return $identifier;
		}

		// escape backticks inside the identifier name
		$identifier = str_replace('`', '``', $identifier);

		// wrap in backticks
		return '`' . $identifier . '`';
	}

	/**
	 * Builds a select clause
	 *
	 * @param array $params List of parameters for the select clause. Check out the defaults for more info.
	 * @return array An array with the query and the bindings
	 */
	public function select(array $params = []): array
	{
		$defaults = [
			'table'    => '',
			'columns'  => '*',
			'join'     => null,
			'distinct' => false,
			'where'    => null,
			'group'    => null,
			'having'   => null,
			'order'    => null,
			'offset'   => 0,
			'limit'    => null,
			'bindings' => []
		];

		$options  = array_merge($defaults, $params);
		$bindings = $options['bindings'];
		$query    = ['SELECT'];

		// select distinct values
		if ($options['distinct'] === true) {
			$query[] = 'DISTINCT';
		}

		// columns
		$query[] = $this->selected($options['table'], $options['columns']);

		// from
		$this->extend($query, $bindings, $this->from($options['table']));

		// joins
		$this->extend($query, $bindings, $this->joins($options['join']));

		// where
		$this->extend($query, $bindings, $this->where($options['where']));

		// group
		$this->extend($query, $bindings, $this->group($options['group']));

		// having
		$this->extend($query, $bindings, $this->having($options['having']));

		// order
		$this->extend($query, $bindings, $this->order($options['order']));

		// offset and limit
		$this->extend($query, $bindings, $this->limit($options['offset'], $options['limit']));

		return [
			'query'    => $this->query($query),
			'bindings' => $bindings
		];
	}

	/**
	 * Creates a columns definition from string or array
	 */
	public function selected(string $table, array|string|null $columns = null): string
	{
		// all columns
		if (empty($columns) === true) {
			return '*';
		}

		// array of columns
		if (is_array($columns) === true) {
			// validate columns
			$result = [];

			foreach ($columns as $column) {
				list($table, $columnPart) = $this->splitIdentifier($table, $column);

				if ($this->validateColumn($table, $columnPart) === true) {
					$result[] = $this->combineIdentifier($table, $columnPart);
				}
			}

			return implode(', ', $result);
		}

		return $columns;
	}

	/**
	 * Splits a (qualified) identifier into table and column
	 *
	 * @param string $table Default table if the identifier is not qualified
	 * @throws \Kirby\Exception\InvalidArgumentException if an invalid identifier is given
	 */
	public function splitIdentifier(string $table, string $identifier): array
	{
		// split by dot, but only outside of quotes
		$parts = preg_split('/(?:`[^`]*`|"[^"]*")(*SKIP)(*F)|\./', $identifier);

		return match (count($parts)) {
			// non-qualified identifier
			1 => [$table, $this->unquoteIdentifier($parts[0])],

			// qualified identifier
			2 => [
				$this->unquoteIdentifier($parts[0]),
				$this->unquoteIdentifier($parts[1])
			],

			// every other number is an error
			default => throw new InvalidArgumentException('Invalid identifier ' . $identifier)
		};
	}

	/**
	 * Returns a query to list the tables of the current database;
	 * the query needs to return rows with a column `name`
	 */
	abstract public function tables(): array;

	/**
	 * Validates and quotes a table name
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if an invalid table name is given
	 */
	public function tableName(string $table): string
	{
		// validate table
		if ($this->database->validateTable($table) === false) {
			throw new InvalidArgumentException('Invalid table ' . $table);
		}

		return $this->quoteIdentifier($table);
	}

	/**
	 * Unquotes an identifier (table *or* column)
	 */
	public function unquoteIdentifier(string $identifier): string
	{
		// remove quotes around the identifier
		if (in_array(Str::substr($identifier, 0, 1), ['"', '`']) === true) {
			$identifier = Str::substr($identifier, 1);
		}

		if (in_array(Str::substr($identifier, -1), ['"', '`']) === true) {
			$identifier = Str::substr($identifier, 0, -1);
		}

		// unescape duplicated quotes
		return str_replace(['""', '``'], ['"', '`'], $identifier);
	}

	/**
	 * Builds an update clause
	 *
	 * @param array $params List of parameters for the update clause. See defaults for more info.
	 */
	public function update(array $params = []): array
	{
		$defaults = [
			'table'    => null,
			'values'   => null,
			'where'    => null,
			'bindings' => []
		];

		$options  = array_merge($defaults, $params);
		$bindings = $options['bindings'];

		// start the query
		$query = ['UPDATE ' . $this->tableName($options['table']) . ' SET'];

		// add the values
		$this->extend($query, $bindings, $this->values($options['table'], $options['values']));

		// add the where clause
		$this->extend($query, $bindings, $this->where($options['where']));

		return [
			'query'    => $this->query($query),
			'bindings' => $bindings
		];
	}

	/**
	 * Validates a given column name in a table
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the column is invalid
	 */
	public function validateColumn(string $table, string $column): bool
	{
		if ($this->database->validateColumn($table, $column) !== true) {
			throw new InvalidArgumentException('Invalid column ' . $column);
		}

		return true;
	}

	/**
	 * Builds a safe list of values for insert, select or update queries
	 *
	 * @param string $table Table name
	 * @param mixed $values A value string or array of values
	 * @param string $separator A separator which should be used to join values
	 * @param bool $set If true builds a set list of values for update clauses
	 * @param bool $enforceQualified Always use fully qualified column names
	 */
	public function values(
		string $table,
		$values,
		string $separator = ', ',
		bool $set = true,
		bool $enforceQualified = false
	): array {
		if (is_array($values) === false) {
			return [
				'query' => $values,
				'bindings' => []
			];
		}

		if ($set === true) {
			return $this->valueSet($table, $values, $separator, $enforceQualified);
		}

		return $this->valueList($table, $values, $separator, $enforceQualified);
	}

	/**
	 * Creates a list of fields and values
	 */
	public function valueList(
		string $table,
		string|array $values,
		string $separator = ',',
		bool $enforceQualified = false
	): array {
		$fields   = [];
		$query    = [];
		$bindings = [];

		foreach ($values as $column => $value) {
			$key = $this->columnName($table, $column, $enforceQualified);

			if ($key === null) {
				continue;
			}

			$fields[] = $key;

			if (in_array($value, static::$literals, true) === true) {
				$query[] = $value ?: 'null';
				continue;
			}

			if (is_array($value) === true) {
				$value = json_encode($value);
			}

			// add the binding
			$bindings[$bindingName = $this->bindingName('value')] = $value;

			// create the query
			$query[] = $bindingName;
		}

		return [
			'query'    => '(' . implode($separator, $fields) . ') VALUES (' . implode($separator, $query) . ')',
			'bindings' => $bindings
		];
	}

	/**
	 * Creates a set of values
	 */
	public function valueSet(
		string $table,
		string|array $values,
		string $separator = ',',
		bool $enforceQualified = false
	): array {
		$query    = [];
		$bindings = [];

		foreach ($values as $column => $value) {
			$key = $this->columnName($table, $column, $enforceQualified);

			if ($key === null) {
				continue;
			}

			if (in_array($value, static::$literals, true) === true) {
				$query[] = $key . ' = ' . ($value ?: 'null');
				continue;
			}

			if (is_array($value) === true) {
				$value = json_encode($value);
			}

			// add the binding
			$bindings[$bindingName = $this->bindingName('value')] = $value;

			// create the query
			$query[] = $key . ' = ' . $bindingName;
		}

		return [
			'query'    => implode($separator, $query),
			'bindings' => $bindings
		];
	}

	public function where(string|array|null $where, array $bindings = []): array
	{
		if (empty($where) === true) {
			return [
				'query'    => null,
				'bindings' => [],
			];
		}

		if (is_string($where) === true) {
			return [
				'query'    => 'WHERE ' . $where,
				'bindings' => $bindings
			];
		}

		$query = [];

		foreach ($where as $key => $value) {
			$binding = $this->bindingName('where_' . $key);
			$bindings[$binding] = $value;

			$query[] = $key . ' = ' . $binding;
		}

		return [
			'query'    => 'WHERE ' . implode(' AND ', $query),
			'bindings' => $bindings
		];
	}
}
