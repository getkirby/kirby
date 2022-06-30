<?php

namespace Kirby\Cms;

/**
 * The Search class extracts the
 * search logic from collections, to
 * provide a more globally usable interface
 * for any searches.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Search
{
	/**
	 * @param string|null $query
	 * @param array $params
	 * @return \Kirby\Cms\Files
	 */
	public static function files(string $query = null, $params = [])
	{
		return App::instance()->site()->index()->files()->search($query, $params);
	}

	/**
	 * Native search method to search for anything within the collection
	 *
	 * @param \Kirby\Cms\Collection $collection
	 * @param string|null $query
	 * @param mixed $params
	 * @return \Kirby\Cms\Collection|bool
	 */
	public static function collection(Collection $collection, string $query = null, $params = [])
	{
		$kirby = App::instance();
		return ($kirby->component('search'))($kirby, $collection, $query, $params);
	}

	/**
	 * @param string|null $query
	 * @param array $params
	 * @return \Kirby\Cms\Pages
	 */
	public static function pages(string $query = null, $params = [])
	{
		return App::instance()->site()->index()->search($query, $params);
	}

	/**
	 * @param string|null $query
	 * @param array $params
	 * @return \Kirby\Cms\Users
	 */
	public static function users(string $query = null, $params = [])
	{
		return App::instance()->users()->search($query, $params);
	}
}
