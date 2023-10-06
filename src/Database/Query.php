<?php

namespace Kirby\Database;

use Closure;
use InvalidArgumentException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Str;

/**
 * The query builder is used by the Database class
 * to build SQL queries with a fluent API
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
	public const ERROR_INVALID_QUERY_METHOD = 0;

	/**
	 * Parent Database object
	 */
	protected Database|null $database = null;

	/**
	 * The object which should be fetched for each row
	 * or function to call for each row
	 */
	protected string|Closure $fetch = Obj::class;

	/**
	 * The iterator class, which should be used for result sets
	 */
	protected string $iterator = Collection::class;

	/**
	 * An array of bindings for the final query
	 */
	protected array $bindings = [];

	/**
	 * The table name
	 */
	protected string $table;

	/**
	 * The name of the primary key column
	 */
	protected string $primaryKeyName = 'id';

	/**
	 * An array with additional join parameters
	 */
	protected array|null $join = null;

	/**
	 * A list of columns, which should be selected
	 */
	protected array|string|null $select = null;

	/**
	 * Boolean for distinct select clauses
	 */
	protected bool|null $distinct = null;

	/**
	 * Boolean for if exceptions should be thrown on failing queries
	 */
	protected bool $fail = false;

	/**
	 * A list of values for update and insert clauses
	 */
	protected array|null $values = null;

	/**
	 * WHERE clause
	 */
	protected $where = null;

	/**
	 * GROUP BY clause
	 */
	protected string|null $group = null;

	/**
	 * HAVING clause
	 */
	protected $having = null;

	/**
	 * ORDER BY clause
	 */
	protected $order = null;

	/**
	 * The offset, which should be applied to the select query
	 */
	protected int $offset = 0;

	/**
	 * The limit, which should be applied to the select query
	 */
	protected int|null $limit = null;

	/**
	 * Boolean to enable query debugging
	 */
	protected bool $debug = false;

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
	protected function reset(): void
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
	 * @return $this
	 */
	public function debug(bool $debug = true): static
	{
		$this->debug = $debug;
		return $this;
	}

	/**
	 * Enables distinct select clauses.
	 *
	 * @return $this
	 */
	public function distinct(bool $distinct = true): static
	{
		$this->distinct = $distinct;
		return $this;
	}

	/**
	 * Enables failing queries.
	 * If enabled queries will no longer fail silently but throw an exception
	 *
	 * @return $this
	 */
	public function fail(bool $fail = true): static
	{
		$this->fail = $fail;
		return $this;
	}

	/**
	 * Sets the object class, which should be fetched;
	 * set this to `'array'` to get a simple array instead of an object;
	 * pass a function that receives the `$data` and the `$key` to generate arbitrary data structures
	 *
	 * @return $this
	 */
	public function fetch(string|callable|Closure $fetch): static
	{
		if (is_callable($fetch) === true) {
			$fetch = Closure::fromCallable($fetch);
		}

		$this->fetch = $fetch;
		return $this;
	}

	/**
	 * Sets the iterator class, which should be used for multiple results
	 * Set this to array to get a simple array instead of an iterator object
	 *
	 * @return $this
	 */
	public function iterator(string $iterator): static
	{
		$this->iterator = $iterator;
		return $this;
	}

	/**
	 * Sets the name of the table, which should be queried
	 *
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException if the table does not exist
	 */
	public function table(string $table): static
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
	 * @return $this
	 */
	public function primaryKeyName(string $primaryKeyName): static
	{
		$this->primaryKeyName = $primaryKeyName;
		return $this;
	}

	/**
	 * Sets the columns, which should be selected from the table
	 * By default all columns will be selected
	 *
	 * @param array|string|null $select Pass either a string of columns or an array
	 * @return $this
	 */
	public function select(array|string|null $select): static
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
	 * @return $this
	 */
	public function join(string $table, string $on, string $type = 'JOIN'): static
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
	 * @return $this
	 */
	public function leftJoin(string $table, string $on): static
	{
		return $this->join($table, $on, 'left join');
	}

	/**
	 * Shortcut for creating a right join clause
	 *
	 * @param string $table Name of the table, which should be joined
	 * @param string $on The on clause for this join
	 * @return $this
	 */
	public function rightJoin(string $table, string $on): static
	{
		return $this->join($table, $on, 'right join');
	}

	/**
	 * Shortcut for creating an inner join clause
	 *
	 * @param string $table Name of the table, which should be joined
	 * @param string $on The on clause for this join
	 * @return $this
	 */
	public function innerJoin($table, $on): static
	{
		return $this->join($table, $on, 'inner join');
	}

	/**
	 * Sets the values which should be used for the update or insert clause
	 *
	 * @param mixed $values Can either be a string or an array of values
	 * @return $this
	 */
	public function values($values = []): static
	{
		if ($values !== null) {
			$this->values = $values;
		}
		return $this;
	}

	/**
	 * Attaches additional bindings to the query.
	 * Also can be used as getter for all attached bindings
	 * by not passing an argument.
	 *
	 * @return array|$this
	 * @psalm-return ($bindings is array ? $this : array)
	 */
	public function bindings(array|null $bindings = null): array|static
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
	 * @return $this
	 */
	public function where(...$args): static
	{
		$this->where = $this->filterQuery($args, $this->where);
		return $this;
	}

	/**
	 * Shortcut to attach a where clause with an OR operator.
	 * Check out the where() method docs for additional info.
	 *
	 * @return $this
	 */
	public function orWhere(...$args): static
	{
		$this->where = $this->filterQuery($args, $this->where, 'OR');
		return $this;
	}

	/**
	 * Shortcut to attach a where clause with an AND operator.
	 * Check out the where() method docs for additional info.
	 *
	 * @return $this
	 */
	public function andWhere(...$args): static
	{
		$this->where = $this->filterQuery($args, $this->where, 'AND');
		return $this;
	}

	/**
	 * Attaches a group by clause
	 *
	 * @return $this
	 */
	public function group(string|null $group = null): static
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
	 * @return $this
	 */
	public function having(...$args): static
	{
		$this->having = $this->filterQuery($args, $this->having);
		return $this;
	}

	/**
	 * Attaches an order clause
	 *
	 * @param string|null $order
	 * @return $this
	 */
	public function order(string $order = null)
	{
		$this->order = $order;
		return $this;
	}

	/**
	 * Sets the offset for select clauses
	 *
	 * @return $this
	 */
	public function offset(int $offset): static
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Sets the limit for select clauses
	 *
	 * @return $this
	 */
	public function limit(int|null $limit = null): static
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Builds the different types of SQL queries
	 * This uses the SQL class to build stuff.
	 *
	 * @param string $type (select, update, insert)
	 * @return array The final query
	 */
	public function build(string $type): array
	{
		$sql = $this->database->sql();

		return match ($type) {
			'select' => $sql->select([
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
			]),
			'update' => $sql->update([
				'table'    => $this->table,
				'where'    => $this->where,
				'values'   => $this->values,
				'bindings' => $this->bindings
			]),
			'insert' => $sql->insert([
				'table'    => $this->table,
				'values'   => $this->values,
				'bindings' => $this->bindings
			]),
			'delete' => $sql->delete([
				'table'    => $this->table,
				'where'    => $this->where,
				'bindings' => $this->bindings
			]),
			default => null
		};
	}

	/**
	 * Builds a count query
	 */
	public function count(): int
	{
		return (int)$this->aggregate('COUNT');
	}

	/**
	 * Builds a max query
	 */
	public function max(string $column): float
	{
		return (float)$this->aggregate('MAX', $column);
	}

	/**
	 * Builds a min query
	 */
	public function min(string $column): float
	{
		return (float)$this->aggregate('MIN', $column);
	}

	/**
	 * Builds a sum query
	 */
	public function sum(string $column): float
	{
		return (float)$this->aggregate('SUM', $column);
	}

	/**
	 * Builds an average query
	 */
	public function avg(string $column): float
	{
		return (float)$this->aggregate('AVG', $column);
	}

	/**
	 * Builds an aggregation query.
	 * This is used by all the aggregation methods above
	 *
	 * @param int $default An optional default value, which should be returned if the query fails
	 */
	public function aggregate(string $method, string $column = '*', int $default = 0)
	{
		// reset the sorting to avoid counting issues
		$this->order = null;

		// validate column
		if ($column !== '*') {
			$sql    = $this->database->sql();
			$column = $sql->columnName($this->table, $column);
		}

		$fetch  = $this->fetch;
		$row    = $this->select($method . '(' . $column . ') as aggregation')->fetch(Obj::class)->first();

		if ($this->debug === true) {
			return $row;
		}

		$result = $row?->get('aggregation') ?? $default;

		$this->fetch($fetch);

		return $result;
	}

	/**
	 * Used as an internal shortcut for firing a db query
	 */
	protected function query(string|array $sql, array $params = [])
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
	 */
	protected function execute(string|array $sql, array $params = [])
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

		$result = $this->database->execute($sql['query'], $sql['bindings']);

		$this->reset();

		return $result;
	}

	/**
	 * Selects only one row from a table
	 */
	public function first(): mixed
	{
		return $this->query($this->offset(0)->limit(1)->build('select'), [
			'fetch'    => $this->fetch,
			'iterator' => 'array',
			'method'   => 'fetch',
		]);
	}

	/**
	 * Selects only one row from a table
	 */
	public function row(): mixed
	{
		return $this->first();
	}

	/**
	 * Selects only one row from a table
	 */
	public function one(): mixed
	{
		return $this->first();
	}

	/**
	 * Automatically adds pagination to a query
	 *
	 * @param int $limit The number of rows, which should be returned for each page
	 * @return object Collection iterator with attached pagination object
	 */
	public function page(int $page, int $limit): object
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
		$collection = $this
			->offset($pagination->offset())
			->limit($pagination->limit())
			->iterator('Kirby\Toolkit\Collection')
			->all();

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
	 */
	public function column(string $column)
	{
		// if there isn't already an explicit order, order by the primary key
		// instead of the column that was requested (which would be implied otherwise)
		if ($this->order === null) {
			$sql        = $this->database->sql();
			$primaryKey = $sql->combineIdentifier($this->table, $this->primaryKeyName);

			$this->order($primaryKey . ' ASC');
		}

		$results = $this->query($this->select([$column])->build('select'), [
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
	 */
	public function findBy(string $column, $value)
	{
		return $this->where([$column => $value])->first();
	}

	/**
	 * Find a single row by its primary key
	 */
	public function find($id)
	{
		return $this->findBy($this->primaryKeyName, $id);
	}

	/**
	 * Fires an insert query
	 *
	 * @param mixed $values You can pass values here or set them with ->values() before
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
	 * @param mixed $values You can pass values here or set them with ->values() before
	 * @param mixed $where You can pass a where clause here or set it with ->where() before
	 */
	public function update($values = null, $where = null): bool
	{
		return $this->execute($this->values($values)->where($where)->build('update'));
	}

	/**
	 * Fires a delete query
	 *
	 * @param mixed $where You can pass a where clause here or set it with ->where() before
	 */
	public function delete($where = null): bool
	{
		return $this->execute($this->where($where)->build('delete'));
	}

	/**
	 * Enables magic queries like findByUsername or findByEmail
	 */
	public function __call(string $method, array $arguments = [])
	{
		if (preg_match('!^findBy([a-z]+)!i', $method, $match)) {
			$column = Str::lower($match[1]);
			return $this->findBy($column, $arguments[0]);
		}
		throw new InvalidArgumentException('Invalid query method: ' . $method, static::ERROR_INVALID_QUERY_METHOD);
	}

	/**
	 * Builder for where and having clauses
	 *
	 * @param array $args Arguments, see where() description
	 * @param mixed $current Current value (like $this->where)
	 */
	protected function filterQuery(array $args, $current, string $mode = 'AND')
	{
		$result = '';

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

					// since the callback uses its own where condition
					// it is necessary to clear/reset the cloned where condition
					$query->where = null;

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
					$predicate = trim(strtoupper($args[1]));
					if (is_array($args[2]) === true) {
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

					// ->where('username', 'like', 'myuser');
					} else {
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
					}
					$this->bindings($bindings);
				}

				break;
		}

		// attach the where clause
		if (empty($current) === false) {
			return $current . ' ' . $mode . ' ' . $result;
		}

		return $result;
	}
}
