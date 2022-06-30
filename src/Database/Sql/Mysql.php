<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Sql;

/**
 * MySQL query builder
 *
 * @package   Kirby Database
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Mysql extends Sql
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
	 * Returns a query to list the tables of the current database;
	 * the query needs to return rows with a column `name`
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
}
