<?php

namespace Kirby\Query;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Query\Runners\Runner;
use Kirby\Toolkit\I18n;

/**
 * The Query class can be used to run expressions on arrays and objects,
 * including their methods with a very simple string-based syntax
 *
 * @package   Kirby Query
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
	public static array $cache = [];
	public static array $entries = [];

	public Runner|string $runner;

	/**
	 * Creates a new Query object
	 */
	public function __construct(
		public string|null $query = null
	) {
		if ($query !== null) {
			$this->query = trim($query);
		}

		$this->runner = App::instance()->option('query.runner', 'legacy');

		if ($this->runner !== 'legacy') {

			if (is_subclass_of($this->runner, Runner::class) === false) {
				throw new InvalidArgumentException("Query runner $this->runner must extend " . Runner::class);
			}

			$this->runner = $this->runner::for($this);
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

	/**
	 * Returns the query result if anything
	 * can be found, otherwise returns null
	 *
	 * @throws \Kirby\Exception\BadMethodCallException If an invalid method is accessed by the query
	 * @throws \Kirby\Exception\InvalidArgumentException If an invalid query runner is set in the config option
	 */
	public function resolve(array|object $data = []): mixed
	{
		if (empty($this->query) === true) {
			return $data;
		}

		// TODO: switch to 'interpreted' as default in v6
		// TODO: remove in v7
		// @codeCoverageIgnoreStart

		if ($this->runner === 'legacy') {
			return $this->resolveLegacy($data);
		}
		// @codeCoverageIgnoreEnd

		return $this->runner->run($this->query, (array)$data);
	}

	/**
	 * @deprecated 5.1.0
	 * @codeCoverageIgnore
	 */
	private function resolveLegacy(array|object $data = []): mixed
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
