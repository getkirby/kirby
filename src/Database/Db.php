<?php

namespace Kirby\Database;

use InvalidArgumentException;
use Kirby\Toolkit\Config;

/**
 * Database shortcuts
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Db
{
    const ERROR_UNKNOWN_METHOD = 0;

    /**
     * Query shortcuts
     *
     * @var array
     */
    public static $queries = [];

    /**
     * The singleton Database object
     *
     * @var Database
     */
    public static $connection = null;

    /**
     * (Re)connect the database
     *
     * @param array $params Pass [] to use the default params from the config
     * @return \Kirby\Database\Database
     */
    public static function connect(array $params = null)
    {
        if ($params === null && static::$connection !== null) {
            return static::$connection;
        }

        // try to connect with the default
        // connection settings if no params are set
        $params = $params ?? [
            'type'     => Config::get('db.type', 'mysql'),
            'host'     => Config::get('db.host', 'localhost'),
            'user'     => Config::get('db.user', 'root'),
            'password' => Config::get('db.password', ''),
            'database' => Config::get('db.database', ''),
            'prefix'   => Config::get('db.prefix', ''),
        ];

        return static::$connection = new Database($params);
    }

    /**
     * Returns the current database connection
     *
     * @return \Kirby\Database\Database
     */
    public static function connection()
    {
        return static::$connection;
    }

    /**
     * Sets the current table, which should be queried. Returns a
     * Query object, which can be used to build a full query for
     * that table.
     *
     * @param string $table
     * @return \Kirby\Database\Query
     */
    public static function table($table)
    {
        $db = static::connect();
        return $db->table($table);
    }

    /**
     * Executes a raw sql query which expects a set of results
     *
     * @param string $query
     * @param array $bindings
     * @param array $params
     * @return mixed
     */
    public static function query(string $query, array $bindings = [], array $params = [])
    {
        $db = static::connect();
        return $db->query($query, $bindings, $params);
    }

    /**
     * Executes a raw sql query which expects no set of results (i.e. update, insert, delete)
     *
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    public static function execute(string $query, array $bindings = [])
    {
        $db = static::connect();
        return $db->execute($query, $bindings);
    }

    /**
     * Magic calls for other static db methods,
     * which are redircted to the database class if available
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        if (isset(static::$queries[$method])) {
            return static::$queries[$method](...$arguments);
        }

        if (is_callable([static::$connection, $method]) === true) {
            return call_user_func_array([static::$connection, $method], $arguments);
        }

        throw new InvalidArgumentException('Invalid static Db method: ' . $method, static::ERROR_UNKNOWN_METHOD);
    }
}

/**
 * Shortcut for select clauses
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $columns Either a string with columns or an array of column names
 * @param mixed $where The where clause. Can be a string or an array
 * @param string $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
Db::$queries['select'] = function (string $table, $columns = '*', $where = null, string $order = null, int $offset = 0, int $limit = null) {
    return Db::table($table)->select($columns)->where($where)->order($order)->offset($offset)->limit($limit)->all();
};

/**
 * Shortcut for selecting a single row in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $columns Either a string with columns or an array of column names
 * @param mixed $where The where clause. Can be a string or an array
 * @param string $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
Db::$queries['first'] = Db::$queries['row'] = Db::$queries['one'] = function (string $table, $columns = '*', $where = null, string $order = null) {
    return Db::table($table)->select($columns)->where($where)->order($order)->first();
};

/**
 * Returns only values from a single column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column to select from
 * @param mixed $where The where clause. Can be a string or an array
 * @param string $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
Db::$queries['column'] = function (string $table, string $column, $where = null, string $order = null, int $offset = 0, int $limit = null) {
    return Db::table($table)->where($where)->order($order)->offset($offset)->limit($limit)->column($column);
};

/**
 * Shortcut for inserting a new row into a table
 *
 * @param string $table The name of the table, which should be queried
 * @param array $values An array of values, which should be inserted
 * @return bool
 */
Db::$queries['insert'] = function (string $table, array $values) {
    return Db::table($table)->insert($values);
};

/**
 * Shortcut for updating a row in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param array $values An array of values, which should be inserted
 * @param mixed $where An optional where clause
 * @return bool
 */
Db::$queries['update'] = function (string $table, array $values, $where = null) {
    return Db::table($table)->where($where)->update($values);
};

/**
 * Shortcut for deleting rows in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $where An optional where clause
 * @return bool
 */
Db::$queries['delete'] = function (string $table, $where = null) {
    return Db::table($table)->where($where)->delete();
};

/**
 * Shortcut for counting rows in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $where An optional where clause
 * @return int
 */
Db::$queries['count'] = function (string $table, $where = null) {
    return Db::table($table)->where($where)->count();
};

/**
 * Shortcut for calculating the minimum value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the minimum should be calculated
 * @param mixed $where An optional where clause
 * @return mixed
 */
Db::$queries['min'] = function (string $table, string $column, $where = null) {
    return Db::table($table)->where($where)->min($column);
};

/**
 * Shortcut for calculating the maximum value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the maximum should be calculated
 * @param mixed $where An optional where clause
 * @return mixed
 */
Db::$queries['max'] = function (string $table, string $column, $where = null) {
    return Db::table($table)->where($where)->max($column);
};

/**
 * Shortcut for calculating the average value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the average should be calculated
 * @param mixed $where An optional where clause
 * @return mixed
 */
Db::$queries['avg'] = function (string $table, string $column, $where = null) {
    return Db::table($table)->where($where)->avg($column);
};

/**
 * Shortcut for calculating the sum of all values in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the sum should be calculated
 * @param mixed $where An optional where clause
 * @return mixed
 */
Db::$queries['sum'] = function (string $table, string $column, $where = null) {
    return Db::table($table)->where($where)->sum($column);
};
