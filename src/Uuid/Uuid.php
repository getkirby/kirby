<?php

namespace Kirby\Uuid;

use Closure;
use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Stringable;

/**
 * The `Uuid` classes provide an interface to connect
 * identifiable models (page, file, site, user, blocks,
 * structure entries) with a dedicated UUID string.
 * It also provides methods to cache these connections
 * for faster lookup.
 *
 * ```php
 * // get UUID string
 * $model->uuid()->toString();
 *
 * // get model from an UUID string
 * Uuid::for('page://HhX1YtRR2ImG6h4')->model();
 *
 * // cache actions
 * $model->uuid()->populate();
 * $model->uuid()->clear();
 * ```
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Uuid implements Stringable
{
	protected const string TYPE = 'uuid';

	/**
	 * Customizable callback function for generating new ID strings instead
	 * of `Str::random()`. Receives length of string as parameter.
	 */
	public static Closure|null $generator = null;

	/**
	 * Collection that is likely to contain the model and
	 * that will be checked first to speed up the lookup
	 */
	public Collection|null $context;

	public Identifiable|null $model;
	public Uri $uri;

	public function __construct(
		string|null $uuid = null,
		Identifiable|null $model = null,
		Collection|null $context = null
	) {
		// throw exception when globally disabled
		if (Uuids::enabled() === false) {
			throw new LogicException(
				message: 'UUIDs have been disabled via the `content.uuid` config option.'
			);
		}


		$this->context = $context;
		$this->model   = $model;

		if ($model) {
			$this->uri = new Uri([
				'scheme' => static::TYPE,
				'host'   => static::retrieveId($model)
			]);

			// in the rare case that both model and ID string
			// got passed, make sure they match
			if ($uuid && $uuid !== $this->uri->toString()) {
				throw new LogicException(
					message: 'UUID: can\'t create new instance from both model and UUID string that do not match'
				);
			}
		} elseif ($uuid) {
			$this->uri = new Uri($uuid);
		}
	}

	/**
	 * Removes the current UUID from cache,
	 * recursively including all children if needed
	 */
	public function clear(bool $recursive = false): bool
	{
		// For all models with children: if $recursive,
		// also clear UUIDs from cache for all children
		if ($recursive === true && $model = $this->model()) {
			if (method_exists($model, 'children') === true) {
				foreach ($model->children() as $child) {
					$child->uuid()->clear(true);
				}
			}
		}

		if ($key = $this->key()) {
			return Uuids::cache()->remove($key);
		}

		return true;
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
	 * Looks up UUID in cache and resolves
	 * to identifiable model object;
	 * implemented on child classes
	 *
	 * @codeCoverageIgnore
	 */
	protected function findByCache(): Identifiable|null
	{
		throw new LogicException(
			message: 'UUID class needs to implement the ::findByCache() method'
		);
	}

	/**
	 * Looks up UUID in local and global index
	 * and returns the identifiable model object;
	 * implemented on child classes
	 *
	 * @codeCoverageIgnore
	 */
	protected function findByIndex(): Identifiable|null
	{
		throw new LogicException(
			message: 'UUID class needs to implement the ::findByIndex() method'
		);
	}

	/**
	 * Creates a UUID object for a model object
	 */
	final public static function for(
		Identifiable $seed,
		Collection|null $context = null
	): static|null {
		if (Uuids::enabled() === false) {
			return null;
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
			// TODO: activate for uuid-block-structure-support
			// $seed instanceof Block
			// 	=> new BlockUuid(model: $seed, context: $context),
			// $seed instanceof StructureObject
			// 	=> new StructureUuid(model: $seed, context: $context),
			default => throw new InvalidArgumentException(
				message: 'UUID not supported for: ' . $seed::class
			)
		};
	}

	/**
	 * Creates a UUID object from a UUID string
	 * @since 6.0.0
	 */
	final public static function from(
		string $uuid,
		string|array|null $scheme = null,
		Collection|null $context = null
	): static|null {
		if (Uuids::enabled() === false) {
			return null;
		}

		if ($scheme !== null && static::is($uuid, $scheme) === false) {
			return null;
		}

		if ($uri = Str::before($uuid, '://')) {
			return match ($uri) {
				'page'   => new PageUuid(uuid: $uuid, context: $context),
				'file'   => new FileUuid(uuid: $uuid, context: $context),
				'site'   => new SiteUuid(uuid: $uuid, context: $context),
				'user'   => new UserUuid(uuid: $uuid, context: $context),
				// TODO: activate for uuid-block-structure-support
				// 'block'  => new BlockUuid(uuid: $seed, context: $context),
				// 'struct' => new StructureUuid(uuid: $seed, context: $context),
				default  => throw new InvalidArgumentException(
					message: 'Invalid UUID URI "' . $uri . '" in ' . $uuid
				)
			};
		}

		throw new InvalidArgumentException(
			message: 'Invalid UUID string: ' . $uuid
		);
	}

	/**
	 * Generates a new ID string
	 */
	final public static function generate(int $length = 16): string
	{
		if (static::$generator !== null) {
			return (static::$generator)($length);
		}

		$option = App::instance()->option('content.uuid');

		if (is_array($option) === true) {
			$option = $option['format'] ?? null;
		}

		if ($option === 'uuid-v4') {
			return Str::uuid();
		}

		return Str::lower(Str::random($length, 'alphaNum'));
	}

	/**
	 * Returns the UUID's id string (UUID without scheme);
	 * in child classes, this method must ensure that the
	 * model has an ID (or generate a new one if the model
	 * does not have one yet)
	 */
	abstract public function id(): string;

	/**
	 * Generator function that creates an index of
	 * all identifiable model objects globally;
	 * implemented in child classes
	 */
	public static function index(): Generator
	{
		yield from [];
	}

	/**
	 * Merges local and global index generators
	 * into one iterator
	 * @internal
	 *
	 * @return \Generator|\Kirby\Uuid\Identifiable[]
	 */
	final public function indexes(): Generator
	{
		yield from $this->context();
		yield from static::index();
	}

	/**
	 * Checks if a string resembles an UUID URI,
	 * optionally of the given type (scheme)
	 */
	final public static function is(
		string $string,
		string|array|null $type = null
	): bool {
		// always return false when UUIDs have been disabled
		if (Uuids::enabled() === false) {
			return false;
		}

		// use all available schemes by default
		$type  ??= Uri::$schemes;
		$type    = implode('|', A::wrap($type));
		$pattern = sprintf('!^(%s)://(.*)!', $type);

		if (preg_match($pattern, $string, $matches) !== 1) {
			return false;
		}

		if ($matches[1] === 'site') {
			return strlen($matches[2]) === 0;
		}

		return strlen($matches[2]) > 0;
	}

	/**
	 * Checks if the UUID has already been cached
	 */
	public function isCached(): bool
	{
		if ($key = $this->key()) {
			return Uuids::cache()->exists($key);
		}

		return false;
	}

	/**
	 * Returns key for cache entry
	 */
	public function key(bool $generate = false): string|null
	{
		// the generation happens in the child class
		// that overrides the `id()` method
		$id = $generate === true ? $this->id() : $this->uri->host();

		if ($id !== null) {
			// for better performance when using a file-based cache,
			// turn first two characters of the id into a directory
			$id =
				static::TYPE . '/' .
				Str::substr($id, 0, 2) . '/' .
				Str::substr($id, 2);
		}

		return $id;
	}

	/**
	 * Tries to find the identifiable model in cache
	 * or index and returns the object
	 *
	 * @param bool $lazy If `true`, only lookup from cache
	 */
	public function model(bool $lazy = false): Identifiable|null
	{
		if ($this->model !== null) {
			return $this->model;
		}

		if ($this->model = $this->findByCache()) {
			return $this->model;
		}

		if ($lazy === false) {
			if (App::instance()->option('content.uuid.index') === false) {
				throw new NotFoundException(
					message: 'Model for UUID ' . $this->uri->toString() . ' could not be found without searching in the site index'
				);
			}

			if ($this->model = $this->findByIndex()) {
				// lazily fill cache by writing to cache
				// whenever looked up from index to speed
				// up future lookups of the same UUID
				// also force to update value again if it is already cached
				$this->populate($this->isCached());

				return $this->model;
			}
		}

		return null;
	}

	/**
	 * Feeds the UUID into the cache
	 */
	public function populate(bool $force = false): bool
	{
		if ($force === false && $this->isCached() === true) {
			return true;
		}

		return Uuids::cache()->set($this->key(true), $this->value());
	}

	/**
	 * Retrieves the existing ID string (UUID without
	 * scheme) for the model;
	 * can be overridden in child classes depending
	 * on how the model stores the UUID
	 */
	public static function retrieveId(Identifiable $model): string|null
	{
		return $model->id();
	}

	/**
	 * Returns the full UUID string including scheme
	 */
	public function toString(): string
	{
		// make sure the id is cached
		// that it can be found again
		// (will also ensure ID is generated if non-existent yet)
		$this->populate();

		return $this->uri->toString();
	}

	/**
	 * Returns the URL of the model, including the query and fragment
	 * @since 5.1.0
	 */
	public function toUrl(): string|null
	{
		$model = $this->model();

		if ($model === null) {
			return null;
		}

		if (method_exists($model, 'url') === false) {
			return null;
		}

		$url  = $model->url();
		$url .= $this->uri->query()->toString(true);

		if ($this->uri->hasFragment() === true) {
			$url .= '#' . $this->uri->fragment();
		}

		return $url;
	}

	/**
	 * @see self::render()
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Returns the type of the UUID
	 * @since 6.0.0
	 */
	public function type(): string
	{
		return static::TYPE;
	}

	/**
	 * Returns value to be stored in cache
	 */
	public function value(): string|array
	{
		return $this->model()->id();
	}
}
