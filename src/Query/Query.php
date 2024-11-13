<?php

namespace Kirby\Query;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Image\QrCode;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Query\Runners\Interpreted;
use Kirby\Toolkit\Query\Runners\Transpiled;

/**
 * The Query class can be used to query arrays and objects,
 * including their methods with a very simple string-based syntax.
 *
 * Namespace structure - what handles what:
 * - Query			Main interface, direct entries
 * - Expression		Simple comparisons (`a ? b :c`)
 * - Segments		Chain of method calls (`site.find('notes').url`)
 * - Segment		Single method call (`find('notes')`)
 * - Arguments		Method call parameters (`'template', '!=', 'note'`)
 * - Argument		Single parameter, resolving into actual types
 *
 * @package   Kirby Query
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
	/**
	 * Default data entries
	 */
	public static array $entries = [];

	/**
	 * Creates a new Query object
	 */
	public function __construct(
		public string|null $query = null
	) {
		if ($query !== null) {
			$this->query = trim($query);
		}
	}

	/**
	 * Creates a new Query object
	 */
	public static function factory(string|null $query): static
	{
		return new static(query: $query);
	}

	/**
	 * Method to help classes that extend Query
	 * to intercept a segment's result.
	 */
	public function intercept(mixed $result): mixed
	{
		return $result;
	}

	private function resolve_legacy(array|object $data = []): mixed
	{
		// merge data with default entries
		if (is_array($data) === true) {
			$data = [...static::$entries, ...$data];
		}

		// direct data array access via key
		if (
			is_array($data) === true &&
			array_key_exists($this->query, $data) === true
		) {
			$value = $data[$this->query];

			if ($value instanceof Closure) {
				$value = $value();
			}

			return $value;
		}

		// loop through all segments to resolve query
		return Expression::factory($this->query, $this)->resolve($data);

	}

	/**
	 * Returns the query result if anything
	 * can be found, otherwise returns null
	 *
	 * @throws \Kirby\Exception\BadMethodCallException If an invalid method is accessed by the query
	 */
	public function resolve(array|object $data = []): mixed
	{
		if (empty($this->query) === true) {
			return $data;
		}

		$mode = App::instance()->option('query.runner', 'interpreted');

		if ($mode === 'legacy') {
			return $this->resolve_legacy($data);
		}

		$runnerClass = match($mode) {
			'transpiled' => Transpiled::class,
			'interpreted' => Interpreted::class,
			default => throw new Exception('Invalid query runner')
		};

		$runner = new $runnerClass(static::$entries, $this->intercept(...));
		return $runner->run($this->query, (array)$data);
	}
}

/**
 * Default entries/functions
 */
Query::$entries['kirby'] = function (): App {
	return App::instance();
};

Query::$entries['collection'] = function (string $name): Collection|null {
	return App::instance()->collection($name);
};

Query::$entries['file'] = function (string $id): File|null {
	return App::instance()->file($id);
};

Query::$entries['page'] = function (string $id): Page|null {
	return App::instance()->page($id);
};

Query::$entries['qr'] = function (string $data): QrCode {
	return new QrCode($data);
};

Query::$entries['site'] = function (): Site {
	return App::instance()->site();
};

Query::$entries['t'] = function (
	string $key,
	string|array|null $fallback = null,
	string|null $locale = null
): string|null {
	return I18n::translate($key, $fallback, $locale);
};

Query::$entries['user'] = function (string|null $id = null): User|null {
	return App::instance()->user($id);
};
