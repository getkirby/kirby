<?php

namespace Kirby\Uuid;

use Closure;
use Generator;
use Hidehalo\Nanoid\Client as Nanoid;
use InvalidArgumentException;
use Kirby\Cms\Block;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;

/**
 * The `Uuid` classes provide an interface to connect
 * indetifiable models (page, file, site, user, blocks,
 * structure entries) with a dedicated UUID string.
 * It also provides methods to cache these connections
 * for faster lookup.
 *
 * ```
 * // get UUID string
 * $model->uuid()->render();
 *
 * // get model from an UUID string
 * Uuid::for('page://HhX1YtRR2ImG6h4')->resolve();
 *
 * // cache actions
 * $model->uuid()->populate();
 * $model->uuid()->clear();
 * ```
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Uuid
{
	protected const TYPE = 'uuid';

	/**
	 * Customisable callback function for generating new ID strings
	 * instead of using Nanoid. Receives length of string as parameter.
	 */
	public static Closure|null $generator = null;

	public Collection|null $context;
	public Identifiable|null $model;
	public Uri|null $uri;

	public function __construct(
		string|null $uuid = null,
		Identifiable|null $model = null,
		Collection|null $context = null
	) {
		$this->context = $context;
		$this->model   = $model;

		if ($model) {
			$this->uri = new Uri([
				'scheme' => static::TYPE,
				'host'   => static::retrieveId($model)
			]);
		} elseif ($uuid) {
			$this->uri = new Uri($uuid);
		}
	}

	/**
	 * Removes Uuid from cache,
	 * recursively if needed
	 */
	public function clear(bool $recursive = false): bool
	{
		// For all models with children: if $recursive,
		// also clear UUIDs rom cache for all children
		if (
			$recursive === true &&
			method_exists($this->model, 'children') === true
		) {
			foreach ($this->model->children() as $child) {
				$child->uuid()->clear(true);
			}
		}

		return Uuids::cache()->remove($this->key());
	}

	/**
	 * Generator function for the local context
	 * collection, which takes priority when looking
	 * up the UUID/model from index
	 * @internal
	 */
	final public function context(): Generator
	{
		yield from $this->context ?? [];
	}

	/**
	 * Look up Uuid in cache and resolve
	 * to identifiable model object.
	 * Implemented on child classes.
	 */
	protected function findByCache(): Identifiable|null
	{
		return null;
	}

	/**
	 * Look up Uuid in local and global index
	 * and return the identifiable model object.
	 * Implemented on child classes.
	 */
	protected function findByIndex(): Identifiable|null
	{
		return null;
	}

	/**
	 * Shorthand to create instance
	 * by passing either UUID or model
	 */
	final public static function for(
		string|Identifiable $seed,
		Collection|null $context = null
	): static {
		if (is_string($seed) === true) {
			return match (Str::before($seed, '://')) {
				'page'   => new PageUuid(uuid: $seed, context: $context),
				'file'   => new FileUuid(uuid: $seed, context: $context),
				'site'   => new SiteUuid(uuid: $seed, context: $context),
				'user'   => new UserUuid(uuid: $seed, context: $context),
				'block'  => new BlockUuid(uuid: $seed, context: $context),
				'struct' => new StructureUuid(uuid: $seed, context: $context),
				default  => throw new InvalidArgumentException('Invalid uuid uri:' . $seed)
			};
		}

		return match (true) {
			$seed instanceof Page
				=> new PageUuid(model: $seed, context: $context),
			$seed instanceof File
				=> new FileUuid(model: $seed, context: $context),
			$seed instanceof Site
				=> new SiteUuid(model: $seed, context: $context),
			$seed instanceof User
				=> new UserUuid(model: $seed, context: $context),
			$seed instanceof Block
				=> new BlockUuid(model: $seed, context: $context),
			$seed instanceof StructureObject
				=> new StructureUuid(model: $seed, context: $context),
			default
			=> throw new InvalidArgumentException('Uuid not supported for:' . get_class($seed))
		};
	}

	/**
	 * Generate a new ID string
	 */
	final public static function generate(int $length = 15): string
	{
		if (static::$generator !== null) {
			return (static::$generator)($length);
		}

		return (new Nanoid())->generateId($length);
	}

	/**
	 * Returns the UUID's id string
	 */
	public function id(): string
	{
		return $this->uri->host();
	}

	/**
	 * Generator function that creates an index of
	 * all identifiable model object globally
	 */
	public static function index(): Generator
	{
		yield from [];
	}

	/**
	 * Merges local and global index generators
	 * into one iterator
	 * @internal
	 */
	final public function indexes(): Generator
	{
		yield from $this->context();
		yield from static::index();
	}

	/**
	 * Checks if a string resembles an UUID uri
	 */
	final public static function is(
		string $string,
		string|null $type = null
	): bool {
		if ($type !== null) {
			return Str::startsWith($string, $type . '://');
		}

		// try to match any of the supported schemes
		$pattern = sprintf('/^(%s):\/\//', implode('|', Uri::$schemes));
		return preg_match($pattern, $string) === 1;
	}

	/**
	 * Checks if Uuid has already been cached
	 */
	public function isCached(): bool
	{
		return Uuids::cache()->exists($this->key());
	}

	/**
	 * Returns key for cache entry
	 */
	public function key(): string
	{
		$id = $this->id();

		// for better performance when using a file-based cache,
		// turn first two characters of the id into a directory
		$id = substr($id, 0, 2) . '/' . substr($id, 2);

		return static::TYPE . '/' . $id;
	}

	/**
	 * Feeds Uuid into the cache
	 *
	 * @return bool
	 */
	public function populate(): bool
	{
		return Uuids::cache()->set($this->key(), $this->value());
	}

	/**
	 * Returns the full UUID string incl. schema
	 */
	public function render(): string
	{
		// make sure id is generated if
		// it doesn't exist yet
		$this->id();

		return $this->uri->toString();
	}

	/**
	 * Tries to find the idetifiable model in cache
	 * or index and return the object
	 */
	public function resolve(bool $lazy = false): Identifiable|null
	{
		if ($this->model !== null) {
			return $this->model;
		}

		if ($this->model = $this->findByCache()) {
			return $this->model;
		}

		if ($lazy === false) {
			if ($this->model = $this->findByIndex()) {
				// lazily fill cache by writing to cache
				// whenever looked up from index
				$this->populate();

				return $this->model;
			}
		}

		return null;
	}

	/**
	 * Retrieves the existing ID string for the model
	 */
	public static function retrieveId(Identifiable $model): string|null
	{
		return $model->id();
	}

	/**
	 * Returns value to be stored in cache
	 */
	public function value(): string
	{
		return $this->resolve()->id();
	}

	/**
	 * @see ::render
	 */
	public function __toString(): string
	{
		return $this->render();
	}
}
