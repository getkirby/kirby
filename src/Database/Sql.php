<?php

namespace Kirby\Database;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * SQL Query builder
 */
class Sql
{

    /**
     * List of literals which should not be escaped in queries
     *
     * @var array
     */
    public static $literals = ['NOW()', null];

    /**
     * The parent database connection
     *
     * @var Database
     */
    public $database;

    /**
     * Constructor
     *
     * @param Database $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Returns a randomly generated binding name
     *
     * @param string $label String that contains lowercase letters and numbers to use as a readable identifier
     * @param string $prefix
     * @return string
     */
    public function bindingName(string $label): string
    {
        // make sure that the binding name is valid to prevent injections
        if (!preg_match('/^[a-z0-9_]+$/', $label)) {
            $label = 'invalid';
        }

        return ':' . $label . '_' . Str::random(16);
    }

    /**
     * Returns a list of columns for a specified table
     * MySQL version
     *
     * @param string $table The table name
     * @return array
     */
    public function columns(string $table): array
    {
        $databaseBinding = $this->bindingName('database');
        $tableBinding    = $this->bindingName('table');

        $query  = 'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS ';
        $query .= 'WHERE TABLE_SCHEMA = ' . $databaseBinding . ' AND TABLE_NAME = ' . $tableBinding;

        return [
            'query'    => $query,
            'bindings' => [
                $databaseBinding => $this->database->name(),
                $tableBinding    => $table,
            ]
        ];
    }

    /**
     * Optionl default value definition for the column
     *
     * @param array $column
     * @return array
     */
    public function columnDefault(array $column): array
    {
        if (isset($column['default']) === false) {
            return [
                'query'    => null,
                'bindings' => []
            ];
        }

        $binding = $this->bindingName($column['name'] . '_default');

        return [
            'query'    => 'DEFAULT ' . $binding,
            'bindings' => [
                $binding = $column['default']
            ]
        ];
    }

    /**
     * Returns a valid column name
     *
     * @param string $table
     * @param string $column
     * @param boolean $enforceQualified
     * @return string|null
     */
    public function columnName(string $table, string $column, bool $enforceQualified = false): ?string
    {
        list($table, $column) = $this->splitIdentifier($table, $column);

        if ($this->validateColumn($table, $column) === true) {
            return $this->combineIdentifier($table, $column, $enforceQualified !== true);
        }

        return null;
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
            'id'        => '{{ name }} INT(11) UNSIGNED NOT NULL AUTO_INCREMENT',
            'varchar'   => '{{ name }} varchar(255) {{ null }} {{ default }}',
            'text'      => '{{ name }} TEXT',
            'int'       => '{{ name }} INT(11) UNSIGNED {{ null }} {{ default }}',
            'timestamp' => '{{ name }} TIMESTAMP {{ null }} {{ default }}'
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
        return [
            'query' => null,
            'bindings' => []
        ];
    }

    /**
     * Combines an identifier (table and column)
     * Default version for MySQL
     *
     * @param $table string
     * @param $column string
     * @param $values boolean Whether the identifier is going to be used for a values clause
     *                        Only relevant for SQLite
     * @return string
     */
    public function combineIdentifier(string $table, string $column, bool $values = false): string
    {
        return $this->quoteIdentifier($table) . '.' . $this->quoteIdentifier($column);
    }

    /**
     * Creates the create syntax for a single column
     *
     * @param string $table
     * @param array $column
     * @return array
     */
    public function createColumn(string $table, array $column): array
    {
        // column type
        if (isset($column['type']) === false) {
            throw new InvalidArgumentException('No column type given for column ' . $column);
        }

        // column name
        if (isset($column['name']) === false) {
            throw new InvalidArgumentException('No column name given');
        }

        if ($column['type'] === 'id') {
            $column['key'] = 'PRIMARY';
        }

        if (!$template = ($this->columnTypes()[$column['type']] ?? null)) {
            throw new InvalidArgumentException('Unsupported column type: ' . $column['type']);
        }

        // null
        if (A::get($column, 'null') === false) {
            $null = 'NOT NULL';
        } else {
            $null = 'NULL';
        }

        // indexes/keys
        $key = false;

        if (isset($column['key']) === true) {
            $column['key'] = strtoupper($column['key']);

            // backwards compatibility
            if ($column['key'] === 'PRIMARY') {
                $column['key'] = 'PRIMARY KEY';
            }

            if (in_array($column['key'], ['PRIMARY KEY', 'INDEX']) === true) {
                $key = $column['key'];
            }
        }

        // default value
        $columnDefault = $this->columnDefault($column);
        $columnKey     = $this->columnKey($column);

        $query = trim(Str::template($template, [
            'name'    => $this->quoteIdentifier($column['name']),
            'null'    => $null,
            'key'     => $columnKey['query'],
            'default' => $columnDefault['query'],
        ]));

        $bindings = array_merge($columnKey['bindings'], $columnDefault['bindings']);

        return [
            'query'    => $query,
            'bindings' => $bindings,
            'key'      => $key
        ];
    }

    /**
     * Creates a table with a simple scheme array for columns
     * Default version for MySQL
     *
     * @param string $table The table name
     * @param array $columns
     * @return array
     */
    public function createTable(string $table, array $columns = []): array
    {
        $output   = [];
        $keys     = [];
        $bindings = [];

        foreach ($columns as $name => $column) {
            $sql = $this->createColumn($table, $column);

            $output[] = $sql['query'];

            if ($sql['key']) {
                $keys[$column['name']] = $sql['key'];
            }

            $bindings = array_merge($bindings, $sql['bindings']);
        }

        // combine columns
        $inner = implode(',' . PHP_EOL, $output);

        // add keys
        foreach ($keys as $name => $key) {
            $inner .= ',' . PHP_EOL . $key . ' (' . $this->quoteIdentifier($name) . ')';
        }

        return [
            'query'    => 'CREATE TABLE ' . $this->quoteIdentifier($table) . ' (' . PHP_EOL . $inner . PHP_EOL . ')',
            'bindings' => $bindings
        ];
    }

    /**
     * Builds a delete clause
     *
     * @param array $params List of parameters for the delete clause. See defaults for more info.
     * @return array
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
     *
     * @param string $table
     * @return array
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
     *
     * @param array $query
     * @param array $bindings
     * @param array $input
     * @return void
     */
    public function extend(&$query, array &$bindings = [], $input)
    {
        if (empty($input['query']) === false) {
            $query[]  = $input['query'];
            $bindings = array_merge($bindings, $input['bindings']);
        }
    }

    /**
     * Creates the from syntax
     *
     * @param string $table
     * @return array
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
     *
     * @param string $group
     * @return array
     */
    public function group(string $group = null): array
    {
        if (empty($group) === true) {
            return [
                'query'    => null,
                'bindings' => []
            ];
        }

        return [
            'query'    => 'GROUP BY ' . $group,
            'bindings' => []
        ];
    }

    /**
     * Creates the having syntax
     *
     * @param string $having
     * @return array
     */
    public function having(string $having = null): array
    {
        if (empty($having) === true) {
            return [
                'query'    => null,
                'bindings' => []
            ];
        }

        return [
            'query'    => 'HAVING ' . $having,
            'bindings' => []
        ];
    }

    /**
     * Creates an insert query
     *
     * @param array $params
     * @return array
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
     * @param string $table
     * @param string $type
     * @param string $on
     * @return array
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
     *
     * @params array $joins
     * @return array
     */
    public function joins(array $joins = null): array
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
     *
     * @param integer $offset
     * @param integer|null $limit
     * @return array
     */
    public function limit(int $offset = 0, int $limit = null): array
    {
        // no need to add it to the query
        if ($offset === 0 && $limit === null) {
            return [
                'query'    => null,
                'bindings' => []
            ];
        }

        $limit = $limit ?? '18446744073709551615';

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
     *
     * @param string $order
     * @return array
     */
    public function order(string $order = null): array
    {
        if (empty($order) === true) {
            return [
                'query'    => null,
                'bindings' => []
            ];
        }

        return [
            'query'    => 'ORDER BY ' . $order,
            'bindings' => []
        ];
    }

    /**
     * Converts a query array into a final string
     *
     * @param array $query
     * @param string $separator
     * @return string
     */
    public function query(array $query, string $separator = ' ')
    {
        return implode($separator, array_filter($query));
    }

    /**
     * Quotes an identifier (table *or* column)
     * Default version for MySQL
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

        // replace every backtick with two backticks
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
     *
     * @param string $table
     * @param array|string|null $columns
     * @return string
     */
    public function selected($table, $columns = null): string
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
        } else {
            return $columns;
        }
    }

    /**
     * Splits a (qualified) identifier into table and column
     *
     * @param $table string Default table if the identifier is not qualified
     * @param $identifier string
     * @return array
     */
    public function splitIdentifier($table, $identifier): array
    {
        // split by dot, but only outside of quotes
        $parts = preg_split('/(?:`[^`]*`|"[^"]*")(*SKIP)(*F)|\./', $identifier);

        switch (count($parts)) {
            // non-qualified identifier
            case 1:
                return array($table, $this->unquoteIdentifier($parts[0]));

            // qualified identifier
            case 2:
                return array($this->unquoteIdentifier($parts[0]), $this->unquoteIdentifier($parts[1]));

            // every other number is an error
            default:
                throw new InvalidArgumentException('Invalid identifier ' . $identifier);
        }
    }

    /**
     * Returns a list of tables for a specified database
     * MySQL version
     *
     * @return array
     */
    public function tables(): array
    {
        $binding = $this->bindingName('database');

        return [
            'query'    => 'SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ' . $binding,
            'bindings' => [
                $binding => $this->database->name()
            ]
        ];
    }

    /**
     * Validates and quotes a table name
     *
     * @param string $table
     * @return string
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
     *
     * @param $identifier string
     * @return string
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
     * @return array
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
     * @param string $table
     * @param string $column
     * @return boolean
     */
    public function validateColumn(string $table, string $column): bool
    {
        if ($this->database->validateColumn($table, $column) === false) {
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
     * @param boolean $set If true builds a set list of values for update clauses
     * @param boolean $enforceQualified Always use fully qualified column names
     */
    public function values(string $table, $values, string $separator = ', ', bool $set = true, bool $enforceQualified = false): array
    {
        if (is_array($values) === false) {
            return [
                'query' => $values,
                'bindings' => []
            ];
        }

        if ($set === true) {
            return $this->valueSet($table, $values, $separator, $enforceQualified);
        } else {
            return $this->valueList($table, $values, $separator, $enforceQualified);
        }
    }

    /**
     * Creates a list of fields and values
     *
     * @param string $table
     * @param string|array $values
     * @param string $separator
     * @param bool $enforceQualified
     * @param array
     */
    public function valueList(string $table, $values, string $separator = ',', bool $enforceQualified = false): array
    {
        $fields   = [];
        $query    = [];
        $bindings = [];

        foreach ($values as $key => $value) {
            $fields[] = $this->columnName($table, $key, $enforceQualified);

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
     *
     * @param string $table
     * @param string|array $values
     * @param string $separator
     * @param bool $enforceQualified
     * @param array
     */
    public function valueSet(string $table, $values, string $separator = ',', bool $enforceQualified = false): array
    {
        $query    = [];
        $bindings = [];

        foreach ($values as $column => $value) {
            $key = $this->columnName($table, $column, $enforceQualified);

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

    /**
     * @param string|array|null $where
     * @param array $bindings
     * @return array
     */
    public function where($where, array $bindings = []): array
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
