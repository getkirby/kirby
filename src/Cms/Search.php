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
	public static function files(
		string $query = null,
		array $params = []
	): Files {
		return App::instance()->site()->index()->files()->search($query, $params);
	}

	/**
	 * Native search method to search for anything within the collection
	 */
	public static function collection(
		Collection $collection,
		string|null $query = null,
		string|array $params = []
	): Collection {
		$kirby = App::instance();
		return ($kirby->component('search'))($kirby, $collection, $query, $params);
	}

	public static function pages(
		string $query = null,
		array $params = []
	): Pages {
		return App::instance()->site()->index()->search($query, $params);
	}

	public static function users(
		string $query = null,
		array $params = []
	): Users {
		return App::instance()->users()->search($query, $params);
	}
}
