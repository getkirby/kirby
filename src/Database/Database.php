<?php

namespace Kirby\Database;

use Closure;
use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use PDO;
use PDOStatement;
use Throwable;

/**
 * A simple database class
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Database
{
	/**
	 * The number of affected rows for the last query
	 */
	protected int|null $affected = null;

	/**
	 * Whitelist for column names
	 */
	protected array $columnWhitelist = [];

	/**
	 * The established connection
	 */
	protected PDO|null $connection = null;

	/**
	 * A global array of started connections
	 */
	public static array $connections = [];

	/**
	 * Database name
	 */
	protected string $database;

	protected string $dsn;

	/**
	 * Set to true to throw exceptions on failed queries
	 */
	protected bool $fail = false;

	/**
	 * The connection id
	 */
	protected string $id;

	/**
	 * The last error
	 */
	protected Throwable|null $lastError = null;

	/**
	 * The last insert id
	 */
	protected int|null $lastId = null;

	/**
	 * The last query
	 */
	protected string $lastQuery;

	/**
	 * The last result set
	 */
	protected $lastResult;

	/**
	 * Optional prefix for table names
	 */
	protected string|null $prefix = null;

	/**
	 * The PDO query statement
	 */
	protected PDOStatement|null $statement = null;

	/**
	 * List of existing tables in the database
	 */
	protected array|null $tables = null;

	/**
	 * An array with all queries which are being made
	 */
	protected array $trace = [];

	/**
	 * The database type (mysql, sqlite)
	 */
	protected string $type;

	public static array $types = [];

	/**
	 * Creates a new Database instance
	 */
	public function __construct(array $params = [])
	{
		$this->connect($params);
	}

	/**
	 * Returns one of the started instances
	 */
	public static function instance(string|null $id = null): static|null
	{
		if ($id === null) {
			return A::last(static::$connections);
		}

		return static::$connections[$id] ?? null;
	}

	/**
	 * Returns all started instances
	 */
	public static function instances(): array
	{
		return static::$connections;
	}

	/**
	 * Connects to a database
	 *
	 * @param array|null $params This can either be a config key or an array of parameters for the connection
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function connect(array|null $params = null): PDO|null
	{
		$defaults = [
			'database' => null,
			'type'     => 'mysql',
			'prefix'   => null,
			'user'     => null,
			'password' => null,
			'id'       => uniqid()
		];

		$options = array_merge($defaults, $params);

		// store the database information
		$this->database = $options['database'];
		$this->type     = $options['type'];
		$this->prefix   = $options['prefix'];
		$this->id       = $options['id'];

		if (isset(static::$types[$this->type]) === false) {
			throw new InvalidArgumentException('Invalid database type: ' . $this->type);
		}

		// fetch the dsn and store it
		$this->dsn = (static::$types[$this->type]['dsn'])($options);

		// try to connect
		$this->connection = new PDO($this->dsn, $options['user'], $options['password']);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		// TODO: behavior without this attribute would be preferrable
		// (actual types instead of all strings) but would be a breaking change
		if ($this->type === 'sqlite') {
			$this->connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
		}

		// store the connection
		static::$connections[$this->id] = $this;

		// return the connection
		return $this->connection;
	}

	/**
	 * Returns the currently active connection
	 */
	public function connection(): PDO|null
	{
		return $this->connection;
	}

	/**
	 * Sets the exception mode
	 *
	 * @return $this
	 */
	public function fail(bool $fail = true): static
	{
		$this->fail = $fail;
		return $this;
	}

	/**
	 * Returns the used database type
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Returns the used table name prefix
	 */
	public function prefix(): string|null
	{
		return $this->prefix;
	}

	/**
	 * Escapes a value to be used for a safe query
	 * NOTE: Prepared statements using bound parameters are more secure and solid
	 */
	public function escape(string $value): string
	{
		return substr($this->connection()->quote($value), 1, -1);
	}

	/**
	 * Adds a value to the db trace and also
	 * returns the entire trace if nothing is specified
	 */
	public function trace(array|null $data = null): array
	{
		// return the full trace
		if ($data === null) {
			return $this->trace;
		}

		// add a new entry to the trace
		$this->trace[] = $data;

		return $this->trace;
	}

	/**
	 * Returns the number of affected rows for the last query
	 */
	public function affected(): int|null
	{
		return $this->affected;
	}

	/**
	 * Returns the last id if available
	 */
	public function lastId(): int|null
	{
		return $this->lastId;
	}

	/**
	 * Returns the last query
	 */
	public function lastQuery(): string|null
	{
		return $this->lastQuery;
	}

	/**
	 * Returns the last set of results
	 */
	public function lastResult()
	{
		return $this->lastResult;
	}

	/**
	 * Returns the last db error
	 */
	public function lastError(): Throwable|null
	{
		return $this->lastError;
	}

	/**
	 * Returns the name of the database
	 */
	public function name(): string|null
	{
		return $this->database;
	}

	/**
	 * Private method to execute database queries.
	 * This is used by the query() and execute() methods
	 */
	protected function hit(string $query, array $bindings = []): bool
	{
		// try to prepare and execute the sql
		try {
			$this->statement = $this->connection->prepare($query);
			$this->statement->execute($bindings);

			$this->affected  = $this->statement->rowCount();
			$this->lastId    = Str::startsWith($query, 'insert ', true) ? $this->connection->lastInsertId() : null;
			$this->lastError = null;

			// store the final sql to add it to the trace later
			$this->lastQuery = $this->statement->queryString;
		} catch (Throwable $e) {
			// store the error
			$this->affected  = 0;
			$this->lastError = $e;
			$this->lastId    = null;
			$this->lastQuery = $query;

			// only throw the extension if failing is allowed
			if ($this->fail === true) {
				throw $e;
			}
		}

		// add a new entry to the singleton trace array
		$this->trace([
			'query'    => $this->lastQuery,
			'bindings' => $bindings,
			'error'    => $this->lastError
		]);

		// return true or false on success or failure
		return $this->lastError === null;
	}

	/**
	 * Executes a sql query, which is expected to return a set of results
	 */
	public function query(string $query, array $bindings = [], array $params = [])
	{
		$defaults = [
			'flag'     => null,
			'method'   => 'fetchAll',
			'fetch'    => 'Kirby\Toolkit\Obj',
			'iterator' => 'Kirby\Toolkit\Collection',
		];

		$options = array_merge($defaults, $params);

		if ($this->hit($query, $bindings) === false) {
			return false;
		}

		// define the default flag for the fetch method
		if (
			$options['fetch'] instanceof Closure ||
			$options['fetch'] === 'array'
		) {
			$flags = PDO::FETCH_ASSOC;
		} else {
			$flags = PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE;
		}

		// add optional flags
		if (empty($options['flag']) === false) {
			$flags |= $options['flag'];
		}

		// set the fetch mode
		if ($options['fetch'] instanceof Closure || $options['fetch'] === 'array') {
			$this->statement->setFetchMode($flags);
		} else {
			$this->statement->setFetchMode($flags, $options['fetch']);
		}

		// fetch that stuff
		$results = $this->statement->{$options['method']}();

		// apply the fetch closure to all results if given
		if ($options['fetch'] instanceof Closure) {
			foreach ($results as $key => $result) {
				$results[$key] = $options['fetch']($result, $key);
			}
		}

		if ($options['iterator'] === 'array') {
			return $this->lastResult = $results;
		}

		return $this->lastResult = new $options['iterator']($results);
	}

	/**
	 * Executes a sql query, which is expected
	 * to not return a set of results
	 */
	public function execute(string $query, array $bindings = []): bool
	{
		return $this->lastResult = $this->hit($query, $bindings);
	}

	/**
	 * Returns the correct Sql generator instance
	 * for the type of database
	 */
	public function sql(): Sql
	{
		$className = static::$types[$this->type]['sql'] ?? 'Sql';
		return new $className($this);
	}

	/**
	 * Sets the current table, which should be queried. Returns a
	 * Query object, which can be used to build a full query
	 * for that table
	 */
	public function table(string $table): Query
	{
		return new Query($this, $this->prefix() . $table);
	}

	/**
	 * Checks if a table exists in the current database
	 */
	public function validateTable(string $table): bool
	{
		if ($this->tables === null) {
			// Get the list of tables from the database
			$sql     = $this->sql()->tables();
			$results = $this->query($sql['query'], $sql['bindings']);

			if ($results) {
				$this->tables = $results->pluck('name');
			} else {
				return false;
			}
		}

		return in_array($table, $this->tables) === true;
	}

	/**
	 * Checks if a column exists in a specified table
	 */
	public function validateColumn(string $table, string $column): bool
	{
		if (isset($this->columnWhitelist[$table]) === false) {
			if ($this->validateTable($table) === false) {
				$this->columnWhitelist[$table] = [];
				return false;
			}

			// Get the column whitelist from the database
			$sql     = $this->sql()->columns($table);
			$results = $this->query($sql['query'], $sql['bindings']);

			if ($results) {
				$this->columnWhitelist[$table] = $results->pluck('name');
			} else {
				return false;
			}
		}

		return in_array($column, $this->columnWhitelist[$table]) === true;
	}

	/**
	 * Creates a new table
	 */
	public function createTable(string $table, array $columns = []): bool
	{
		$sql     = $this->sql()->createTable($table, $columns);
		$queries = Str::split($sql['query'], ';');

		foreach ($queries as $query) {
			$query = trim($query);

			if ($this->execute($query, $sql['bindings']) === false) {
				return false;
			}
		}

		// update cache
		if (in_array($table, $this->tables ?? []) !== true) {
			$this->tables[] = $table;
		}

		return true;
	}

	/**
	 * Drops a table
	 */
	public function dropTable(string $table): bool
	{
		$sql = $this->sql()->dropTable($table);
		if ($this->execute($sql['query'], $sql['bindings']) !== true) {
			return false;
		}

		// update cache
		$key = array_search($table, $this->tables ?? []);
		if ($key !== false) {
			unset($this->tables[$key]);
		}

		return true;
	}

	/**
	 * Magic way to start queries for tables by
	 * using a method named like the table.
	 * I.e. $db->users()->all()
	 */
	public function __call(string $method, mixed $arguments = null): Query
	{
		return $this->table($method);
	}
}

/**
 * MySQL database connector
 */
Database::$types['mysql'] = [
	'sql' => 'Kirby\Database\Sql\Mysql',
	'dsn' => function (array $params): string {
		if (isset($params['host']) === false && isset($params['socket']) === false) {
			throw new InvalidArgumentException('The mysql connection requires either a "host" or a "socket" parameter');
		}

		if (isset($params['database']) === false) {
			throw new InvalidArgumentException('The mysql connection requires a "database" parameter');
		}

		$parts = [];

		if (empty($params['host']) === false) {
			$parts[] = 'host=' . $params['host'];
		}

		if (empty($params['port']) === false) {
			$parts[] = 'port=' . $params['port'];
		}

		if (empty($params['socket']) === false) {
			$parts[] = 'unix_socket=' . $params['socket'];
		}

		if (empty($params['database']) === false) {
			$parts[] = 'dbname=' . $params['database'];
		}

		$parts[] = 'charset=' . ($params['charset'] ?? 'utf8');

		return 'mysql:' . implode(';', $parts);
	}
];

/**
 * SQLite database connector
 */
Database::$types['sqlite'] = [
	'sql' => 'Kirby\Database\Sql\Sqlite',
	'dsn' => function (array $params): string {
		if (isset($params['database']) === false) {
			throw new InvalidArgumentException('The sqlite connection requires a "database" parameter');
		}

		return 'sqlite:' . $params['database'];
	}
];
