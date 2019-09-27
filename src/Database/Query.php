<?php

namespace Kirby\Database;

use InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Str;

/**
 * The query builder is used by the Database class
 * to build SQL queries with a fluent API
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
    const ERROR_INVALID_QUERY_METHOD = 0;

    /**
     * Parent Database object
     *
     * @var Database
     */
    protected $database = null;

    /**
     * The object which should be fetched for each row
     *
     * @var string
     */
    protected $fetch = 'Kirby\Toolkit\Obj';

    /**
     * The iterator class, which should be used for result sets
     *
     * @var string
     */
    protected $iterator = 'Kirby\Toolkit\Collection';

    /**
     * An array of bindings for the final query
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The table name
     *
     * @var string
     */
    protected $table;

    /**
     * The name of the primary key column
     *
     * @var string
     */
    protected $primaryKeyName = 'id';

    /**
     * An array with additional join parameters
     *
     * @var array
     */
    protected $join;

    /**
     * A list of columns, which should be selected
     *
     * @var array|string
     */
    protected $select;

    /**
     * Boolean for distinct select clauses
     *
     * @var bool
     */
    protected $distinct;

    /**
     * Boolean for if exceptions should be thrown on failing queries
     *
     * @var bool
     */
    protected $fail = false;

    /**
     * A list of values for update and insert clauses
     *
     * @var array
     */
    protected $values;

    /**
     * WHERE clause
     *
     * @var mixed
     */
    protected $where;

    /**
     * GROUP BY clause
     *
     * @var mixed
     */
    protected $group;

    /**
     * HAVING clause
     *
     * @var mixed
     */
    protected $having;

    /**
     * ORDER BY clause
     *
     * @var mixed
     */
    protected $order;

    /**
     * The offset, which should be applied to the select query
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * The limit, which should be applied to the select query
     *
     * @var int
     */
    protected $limit;

    /**
     * Boolean to enable query debugging
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Constructor
     *
     * @param \Kirby\Database\Database $database Database object
     * @param string $table Optional name of the table, which should be queried
     */
    public function __construct(Database $database, string $table)
    {
        $this->database = $database;
        $this->table($table);
    }

    /**
     * Reset the query class after each db hit
     */
    protected function reset()
    {
        $this->bindings = [];
        $this->join     = null;
        $this->select   = null;
        $this->distinct = null;
        $this->fail     = false;
        $this->values   = null;
        $this->where    = null;
        $this->group    = null;
        $this->having   = null;
        $this->order    = null;
        $this->offset   = 0;
        $this->limit    = null;
        $this->debug    = false;
    }

    /**
     * Enables query debugging.
     * If enabled, the query will return an array with all important info about
     * the query instead of actually executing the query and returning results
     *
     * @param bool $debug
     * @return \Kirby\Database\Query
     */
    public function debug(bool $debug = true)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Enables distinct select clauses.
     *
     * @param bool $distinct
     * @return \Kirby\Database\Query
     */
    public function distinct(bool $distinct = true)
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * Enables failing queries.
     * If enabled queries will no longer fail silently but throw an exception
     *
     * @param bool $fail
     * @return \Kirby\Database\Query
     */
    public function fail(bool $fail = true)
    {
        $this->fail = $fail;
        return $this;
    }

    /**
     * Sets the object class, which should be fetched
     * Set this to array to get a simple array instead of an object
     *
     * @param string $fetch
     * @return \Kirby\Database\Query
     */
    public function fetch(string $fetch)
    {
        $this->fetch = $fetch;
        return $this;
    }

    /**
     * Sets the iterator class, which should be used for multiple results
     * Set this to array to get a simple array instead of an iterator object
     *
     * @param string $iterator
     * @return \Kirby\Database\Query
     */
    public function iterator(string $iterator)
    {
        $this->iterator = $iterator;
        return $this;
    }

    /**
     * Sets the name of the table, which should be queried
     *
     * @param string $table
     * @return \Kirby\Database\Query
     */
    public function table(string $table)
    {
        if ($this->database->validateTable($table) === false) {
            throw new InvalidArgumentException('Invalid table: ' . $table);
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Sets the name of the primary key column
     *
     * @param string $primaryKeyName
     * @return \Kirby\Database\Query
     */
    public function primaryKeyName(string $primaryKeyName)
    {
        $this->primaryKeyName = $primaryKeyName;
        return $this;
    }

    /**
     * Sets the columns, which should be selected from the table
     * By default all columns will be selected
     *
     * @param mixed $select Pass either a string of columns or an array
     * @return \Kirby\Database\Query
     */
    public function select($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Adds a new join clause to the query
     *
     * @param string $table Name of the table, which should be joined
     * @param string $on The on clause for this join
     * @param string $type The join type. Uses an inner join by default
     * @return object
     */
    public function join(string $table, string $on, string $type = 'JOIN')
    {
        $join = [
            'table' => $table,
            'on'    => $on,
            'type'  => $type
        ];

        $this->join[] = $join;
        return $this;
    }

    /**
     * Shortcut for creating a left join clause
     *
     * @param string $table Name of the table, which should be joined
     * @param string $on The on clause for this join
     * @return \Kirby\Database\Query
     */
    public function leftJoin(string $table, string $on)
    {
        return $this->join($table, $on, 'left');
    }

    /**
     * Shortcut for creating a right join clause
     *
     * @param string $table Name of the table, which should be joined
     * @param string $on The on clause for this join
     * @return \Kirby\Database\Query
     */
    public function rightJoin(string $table, string $on)
    {
        return $this->join($table, $on, 'right');
    }

    /**
     * Shortcut for creating an inner join clause
     *
     * @param string $table Name of the table, which should be joined
     * @param string $on The on clause for this join
     * @return \Kirby\Database\Query
     */
    public function innerJoin($table, $on)
    {
        return $this->join($table, $on, 'inner');
    }

    /**
     * Sets the values which should be used for the update or insert clause
     *
     * @param mixed $values Can either be a string or an array of values
     * @return \Kirby\Database\Query
     */
    public function values($values = [])
    {
        if ($values !== null) {
            $this->values = $values;
        }
        return $this;
    }

    /**
     * Attaches additional bindings to the query.
     * Also can be used as getter for all attached bindings by not passing an argument.
     *
     * @param mixed $bindings Array of bindings or null to use this method as getter
     * @return array|Query
     */
    public function bindings(array $bindings = null)
    {
        if (is_array($bindings) === true) {
            $this->bindings = array_merge($this->bindings, $bindings);
            return $this;
        }

        return $this->bindings;
    }

    /**
     * Attaches an additional where clause
     *
     * All available ways to add where clauses
     *
     * ->where('username like "myuser"');                        (args: 1)
     * ->where(['username' => 'myuser']);                   (args: 1)
     * ->where(function($where) { $where->where('id', '=', 1) }) (args: 1)
     * ->where('username like ?', 'myuser')                      (args: 2)
     * ->where('username', 'like', 'myuser');                    (args: 3)
     *
     * @param mixed ...$args
     * @return \Kirby\Database\Query
     */
    public function where(...$args)
    {
        $this->where = $this->filterQuery($args, $this->where);
        return $this;
    }

    /**
     * Shortcut to attach a where clause with an OR operator.
     * Check out the where() method docs for additional info.
     *
     * @param mixed ...$args
     * @return \Kirby\Database\Query
     */
    public function orWhere(...$args)
    {
        $mode = A::last($args);

        // if there's a where clause mode attribute attached…
        if (in_array($mode, ['AND', 'OR']) === true) {
            // remove that from the list of arguments
            array_pop($args);
        }

        // make sure to always attach the OR mode indicator
        $args[] = 'OR';

        $this->where(...$args);
        return $this;
    }

    /**
     * Shortcut to attach a where clause with an AND operator.
     * Check out the where() method docs for additional info.
     *
     * @param mixed ...$args
     * @return \Kirby\Database\Query
     */
    public function andWhere(...$args)
    {
        $mode = A::last($args);

        // if there's a where clause mode attribute attached…
        if (in_array($mode, ['AND', 'OR']) === true) {
            // remove that from the list of arguments
            array_pop($args);
        }

        // make sure to always attach the AND mode indicator
        $args[] = 'AND';

        $this->where(...$args);
        return $this;
    }

    /**
     * Attaches a group by clause
     *
     * @param string $group
     * @return \Kirby\Database\Query
     */
    public function group(string $group = null)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Attaches an additional having clause
     *
     * All available ways to add having clauses
     *
     * ->having('username like "myuser"');                           (args: 1)
     * ->having(['username' => 'myuser']);                      (args: 1)
     * ->having(function($having) { $having->having('id', '=', 1) }) (args: 1)
     * ->having('username like ?', 'myuser')                         (args: 2)
     * ->having('username', 'like', 'myuser');                       (args: 3)
     *
     * @param mixed ...$args
     * @return \Kirby\Database\Query
     */
    public function having(...$args)
    {
        $this->having = $this->filterQuery($args, $this->having);
        return $this;
    }

    /**
     * Attaches an order clause
     *
     * @param string $order
     * @return \Kirby\Database\Query
     */
    public function order(string $order = null)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Sets the offset for select clauses
     *
     * @param int $offset
     * @return \Kirby\Database\Query
     */
    public function offset(int $offset = null)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Sets the limit for select clauses
     *
     * @param int $limit
     * @return \Kirby\Database\Query
     */
    public function limit(int $limit = null)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Builds the different types of SQL queries
     * This uses the SQL class to build stuff.
     *
     * @param string $type (select, update, insert)
     * @return string The final query
     */
    public function build($type)
    {
        $sql = $this->database->sql();

        switch ($type) {
            case 'select':
                return $sql->select([
                    'table'    => $this->table,
                    'columns'  => $this->select,
                    'join'     => $this->join,
                    'distinct' => $this->distinct,
                    'where'    => $this->where,
                    'group'    => $this->group,
                    'having'   => $this->having,
                    'order'    => $this->order,
                    'offset'   => $this->offset,
                    'limit'    => $this->limit,
                    'bindings' => $this->bindings
                ]);
            case 'update':
                return $sql->update([
                    'table'    => $this->table,
                    'where'    => $this->where,
                    'values'   => $this->values,
                    'bindings' => $this->bindings
                ]);
            case 'insert':
                return $sql->insert([
                    'table'    => $this->table,
                    'values'   => $this->values,
                    'bindings' => $this->bindings
                ]);
            case 'delete':
                return $sql->delete([
                    'table'    => $this->table,
                    'where'    => $this->where,
                    'bindings' => $this->bindings
                ]);
        }
    }

    /**
     * Builds a count query
     *
     * @return \Kirby\Database\Query
     */
    public function count()
    {
        return $this->aggregate('COUNT');
    }

    /**
     * Builds a max query
     *
     * @param string $column
     * @return \Kirby\Database\Query
     */
    public function max(string $column)
    {
        return $this->aggregate('MAX', $column);
    }

    /**
     * Builds a min query
     *
     * @param string $column
     * @return \Kirby\Database\Query
     */
    public function min(string $column)
    {
        return $this->aggregate('MIN', $column);
    }

    /**
     * Builds a sum query
     *
     * @param string $column
     * @return \Kirby\Database\Query
     */
    public function sum(string $column)
    {
        return $this->aggregate('SUM', $column);
    }

    /**
     * Builds an average query
     *
     * @param string $column
     * @return \Kirby\Database\Query
     */
    public function avg(string $column)
    {
        return $this->aggregate('AVG', $column);
    }

    /**
     * Builds an aggregation query.
     * This is used by all the aggregation methods above
     *
     * @param string $method
     * @param string $column
     * @param string $default An optional default value, which should be returned if the query fails
     * @return mixed
     */
    public function aggregate(string $method, string $column = '*', $default = 0)
    {
        // reset the sorting to avoid counting issues
        $this->order = null;

        // validate column
        if ($column !== '*') {
            $sql    = $this->database->sql();
            $column = $sql->columnName($this->table, $column);
        }

        $fetch  = $this->fetch;
        $row    = $this->select($method . '(' . $column . ') as aggregation')->fetch('Obj')->first();

        if ($this->debug === true) {
            return $row;
        }

        $result = $row ? $row->get('aggregation') : $default;

        $this->fetch($fetch);

        return $result;
    }

    /**
     * Used as an internal shortcut for firing a db query
     *
     * @param string|array $sql
     * @param array $params
     * @return mixed
     */
    protected function query($sql, array $params = [])
    {
        if (is_string($sql) === true) {
            $sql = [
                'query'    => $sql,
                'bindings' => $this->bindings()
            ];
        }

        if ($this->debug) {
            return [
                'query'    => $sql['query'],
                'bindings' => $this->bindings(),
                'options'  => $params
            ];
        }

        if ($this->fail) {
            $this->database->fail();
        }

        $result = $this->database->query($sql['query'], $sql['bindings'], $params);

        $this->reset();

        return $result;
    }

    /**
     * Used as an internal shortcut for executing a db query
     *
     * @param string|array $sql
     * @param array $params
     * @return mixed
     */
    protected function execute($sql, array $params = [])
    {
        if (is_string($sql) === true) {
            $sql = [
                'query'    => $sql,
                'bindings' => $this->bindings()
            ];
        }

        if ($this->debug === true) {
            return [
                'query'    => $sql['query'],
                'bindings' => $sql['bindings'],
                'options'  => $params
            ];
        }

        if ($this->fail) {
            $this->database->fail();
        }

        $result = $this->database->execute($sql['query'], $sql['bindings'], $params);

        $this->reset();

        return $result;
    }

    /**
     * Selects only one row from a table
     *
     * @return object
     */
    public function first()
    {
        return $this->query($this->offset(0)->limit(1)->build('select'), [
            'fetch'    => $this->fetch,
            'iterator' => 'array',
            'method'   => 'fetch',
        ]);
    }

    /**
     * Selects only one row from a table
     *
     * @return object
     */
    public function row()
    {
        return $this->first();
    }

    /**
     * Selects only one row from a table
     *
     * @return object
     */
    public function one()
    {
        return $this->first();
    }

    /**
     * Automatically adds pagination to a query
     *
     * @param int $page
     * @param int $limit The number of rows, which should be returned for each page
     * @return object Collection iterator with attached pagination object
     */
    public function page(int $page, int $limit)
    {
        // clone this to create a counter query
        $counter = clone $this;

        // count the total number of rows for this query
        $count = $counter->debug(false)->count();

        // pagination
        $pagination = new Pagination([
            'limit' => $limit,
            'page'  => $page,
            'total' => $count,
        ]);

        // apply it to the dataset and retrieve all rows. make sure to use Collection as the iterator to be able to attach the pagination object
        $iterator   = $this->iterator;
        $collection = $this->offset($pagination->offset())->limit($pagination->limit())->iterator('Collection')->all();

        $this->iterator($iterator);

        // return debug information if debug mode is active
        if ($this->debug) {
            $collection['totalcount'] = $count;
            return $collection;
        }

        // store all pagination vars in a separate object
        if ($collection) {
            $collection->paginate($pagination);
        }

        // return the limited collection
        return $collection;
    }

    /**
     * Returns all matching rows from a table
     *
     * @return mixed
     */
    public function all()
    {
        return $this->query($this->build('select'), [
            'fetch'    => $this->fetch,
            'iterator' => $this->iterator,
        ]);
    }

    /**
     * Returns only values from a single column
     *
     * @param string $column
     * @return mixed
     */
    public function column($column)
    {
        $sql        = $this->database->sql();
        $primaryKey = $sql->combineIdentifier($this->table, $this->primaryKeyName);

        $results = $this->query($this->select([$column])->order($primaryKey . ' ASC')->build('select'), [
            'iterator' => 'array',
            'fetch'    => 'array',
        ]);

        if ($this->debug === true) {
            return $results;
        }

        $results = array_column($results, $column);

        if ($this->iterator === 'array') {
            return $results;
        }

        $iterator = $this->iterator;

        return new $iterator($results);
    }

    /**
     * Find a single row by column and value
     *
     * @param string $column
     * @param mixed $value
     * @return mixed
     */
    public function findBy($column, $value)
    {
        return $this->where([$column => $value])->first();
    }

    /**
     * Find a single row by its primary key
     *
     * @param mixed $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->findBy($this->primaryKeyName, $id);
    }

    /**
     * Fires an insert query
     *
     * @param array $values You can pass values here or set them with ->values() before
     * @return mixed Returns the last inserted id on success or false.
     */
    public function insert($values = null)
    {
        $query = $this->execute($this->values($values)->build('insert'));

        if ($this->debug === true) {
            return $query;
        }

        return $query ? $this->database->lastId() : false;
    }

    /**
     * Fires an update query
     *
     * @param array $values You can pass values here or set them with ->values() before
     * @param mixed $where You can pass a where clause here or set it with ->where() before
     * @return bool
     */
    public function update($values = null, $where = null)
    {
        return $this->execute($this->values($values)->where($where)->build('update'));
    }

    /**
     * Fires a delete query
     *
     * @param mixed $where You can pass a where clause here or set it with ->where() before
     * @return bool
     */
    public function delete($where = null)
    {
        return $this->execute($this->where($where)->build('delete'));
    }

    /**
     * Enables magic queries like findByUsername or findByEmail
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        if (preg_match('!^findBy([a-z]+)!i', $method, $match)) {
            $column = Str::lower($match[1]);
            return $this->findBy($column, $arguments[0]);
        } else {
            throw new InvalidArgumentException('Invalid query method: ' . $method, static::ERROR_INVALID_QUERY_METHOD);
        }
    }

    /**
     * Builder for where and having clauses
     *
     * @param array $args Arguments, see where() description
     * @param string $current Current value (like $this->where)
     * @return string
     */
    protected function filterQuery($args, $current)
    {
        $mode   = A::last($args);
        $result = '';

        // if there's a where clause mode attribute attached…
        if (in_array($mode, ['AND', 'OR'])) {
            // remove that from the list of arguments
            array_pop($args);
        } else {
            $mode = 'AND';
        }

        switch (count($args)) {
            case 1:

                if ($args[0] === null) {
                    return $current;

                // ->where('username like "myuser"');
                } elseif (is_string($args[0]) === true) {

                    // simply add the entire string to the where clause
                    // escaping or using bindings has to be done before calling this method
                    $result = $args[0];

                // ->where(['username' => 'myuser']);
                } elseif (is_array($args[0]) === true) {

                    // simple array mode (AND operator)
                    $sql = $this->database->sql()->values($this->table, $args[0], ' AND ', true, true);

                    $result = $sql['query'];

                    $this->bindings($sql['bindings']);
                } elseif (is_callable($args[0]) === true) {
                    $query = clone $this;
                    call_user_func($args[0], $query);

                    // copy over the bindings from the nested query
                    $this->bindings = array_merge($this->bindings, $query->bindings);

                    $result = '(' . $query->where . ')';
                }

                break;
            case 2:

                // ->where('username like :username', ['username' => 'myuser'])
                if (is_string($args[0]) === true && is_array($args[1]) === true) {

                    // prepared where clause
                    $result = $args[0];

                    // store the bindings
                    $this->bindings($args[1]);

                // ->where('username like ?', 'myuser')
                } elseif (is_string($args[0]) === true && is_string($args[1]) === true) {

                    // prepared where clause
                    $result = $args[0];

                    // store the bindings
                    $this->bindings([$args[1]]);
                }

                break;
            case 3:

                // ->where('username', 'like', 'myuser');
                if (is_string($args[0]) === true && is_string($args[1]) === true) {

                    // validate column
                    $sql = $this->database->sql();
                    $key = $sql->columnName($this->table, $args[0]);

                    // ->where('username', 'in', ['myuser', 'myotheruser']);
                    if (is_array($args[2]) === true) {
                        $predicate = trim(strtoupper($args[1]));

                        if (in_array($predicate, ['IN', 'NOT IN']) === false) {
                            throw new InvalidArgumentException('Invalid predicate ' . $predicate);
                        }

                        // build a list of bound values
                        $values   = [];
                        $bindings = [];

                        foreach ($args[2] as $value) {
                            $valueBinding = $sql->bindingName('value');
                            $bindings[$valueBinding] = $value;
                            $values[] = $valueBinding;
                        }

                        // add that to the where clause in parenthesis
                        $result = $key . ' ' . $predicate . ' (' . implode(', ', $values) . ')';

                        $this->bindings($bindings);

                    // ->where('username', 'like', 'myuser');
                    } else {
                        $predicate  = trim(strtoupper($args[1]));
                        $predicates = [
                            '=', '>=', '>', '<=', '<', '<>', '!=', '<=>',
                            'IS', 'IS NOT',
                            'BETWEEN', 'NOT BETWEEN',
                            'LIKE', 'NOT LIKE',
                            'SOUNDS LIKE',
                            'REGEXP', 'NOT REGEXP'
                        ];

                        if (in_array($predicate, $predicates) === false) {
                            throw new InvalidArgumentException('Invalid predicate/operator ' . $predicate);
                        }

                        $valueBinding = $sql->bindingName('value');
                        $bindings[$valueBinding] = $args[2];

                        $result = $key . ' ' . $predicate . ' ' . $valueBinding;

                        $this->bindings($bindings);
                    }
                }

                break;

        }

        // attach the where clause
        if (empty($current) === false) {
            return $current . ' ' . $mode . ' ' . $result;
        } else {
            return $result;
        }
    }
}
