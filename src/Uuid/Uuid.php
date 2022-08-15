<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * The `Uuid` class provides an interface to connect
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
 * Uuid::for($model)->populate();
 * Uuid::for($model)->clear();
 * Index::populate();
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
	protected Cache|null $cache = null;
	protected Collection|null $context;
	protected Uri|null $uri;
	protected Identifiable|null $model;

	public function __construct(
		string|null $uuid = null,
		Identifiable|null $model = null,
		Collection|null $context = null
	) {
		$this->context = $context;
		$this->model   = $model;

		// if UUID string is passed
		if ($uuid) {
			$this->uri = new Uri($uuid);
		}

		// if object is provided to create instance
		if ($model) {
			$type = match (true) {
				$model instanceof Site => 'site',
				$model instanceof Page => 'page',
				$model instanceof File => 'file',
				$model instanceof User => 'user',
				// @codeCoverageIgnoreStart
				$model instanceof Block => 'block',
				$model instanceof StructureObject => 'struct'
				// @codeCoverageIgnoreEnd
			};

			$this->uri = new Uri([
				'scheme' => $type,
				'host'   => Id::get($model)
			]);
		}
	}


	/**
	 * Returns (and initiates) UUID cache object
	 */
	public function cache(): Cache
	{
		return $this->cache ??= new Cache($this);
	}

	/**
	 * Returns the collection of models that serve
	 * as context for looking up an UUID (will first
	 * search in context collection before searching
	 * in global index)
	 */
	public function context(): Collection|null
	{
		return $this->context;
	}

	/**
	 * Creates a new UUID id string and updates
	 * the model's content file to store it permanently
	 */
	protected function create(): string
	{
		if (is_a($this->model, ModelWithContent::class) === true) {
			// generate ID and write to content file
			$id = Id::generate();
			$this->model = Id::write($this->model, $id);
			// update the Uri object
			$this->uri->host($id);
			return $id;
		}

		throw new LogicException('Can only create and write ID string to model with content');
	}

	/**
	 * Clears UUID from cache
	 */
	public function clear(bool $recursive = false): bool
	{
		// For pages: if $recursive, also clear UUIDs
		// from cache for all children recursively
		if ($recursive === true && $this->type() === 'page') {
			foreach ($this->model->children() as $child) {
				$child->uuid()->clear(true);
			}
		}

		return $this->cache()->clear();
	}

	/**
	 * Shorthand to create instance
	 * by passing either UUID or model
	 */
	public static function for(
		string|Identifiable $seed,
		Collection|null $context = null
	): static {
		if (is_string($seed) === true) {
			return new static(uuid: $seed, context: $context);
		}

		return new static(model: $seed, context: $context);
	}

	/**
	 * Returns the UUID's id string. If not set yet,
	 * creates a neq unique ID and writes it to content file
	 */
	public function id(): string
	{
		return $this->uri->host() ?? $this->create();
	}

	/**
	 * Checks if a string resembles an UUID uri
	 */
	public static function is(string $string, string|null $type = null): bool
	{
		if ($type !== null) {
			return Str::startsWith($string, $type . '://');
		}

		// try to match any of the supported schemes
		$pattern = sprintf('/^(%s):\/\//', implode('|', Uri::$schemes));
		return preg_match($pattern, $string) === 1;
	}

	/**
	 * Whether the UUID has been already cached
	 */
	public function isCached(): bool
	{
		return $this->cache()->exists();
	}

	/**
	 * Write UUID to cache
	 */
	public function populate(): bool
	{
		return $this->cache()->populate();
	}


	/**
	 * Returns the full UUID string incl. schema
	 */
	public function render(): string
	{
		// trigger delayed ID generation
		$this->id();

		return $this->uri->toString();
	}

	/**
	 * Tries to find the idetifiable model in cache
	 * or index and return the object
	 */
	public function resolve(bool $lazy = false): Identifiable|null
	{
		// @codeCoverageIgnoreStart
		if ($this->model !== null) {
			return $this->model;
		}
		// @codeCoverageIgnoreEnd

		if ($this->type() === 'site') {
			return $this->model = App::instance()->site();
		}

		if ($this->type() === 'user') {
			return $this->model = App::instance()->user($this->id());
		}

		if ($this->model = Cache::find($this)) {
			return $this->model;
		}

		if ($lazy === false) {
			if ($this->model = Index::find($this)) {
				// lazily fill cache by writing to cache
				// whenever looked up from index
				$this->populate();

				return $this->model;
			}
		}

		return null;
	}

	/**
	 * Returns the model type
	 */
	public function type(): string
	{
		return $this->uri->type();
	}

	/**
	 * Returns permalink url
	 */
	public function url(): string
	{
		$site = App::instance()->site()->url();
		return $site . '/@/' . $this->type() . '/' . $this->id();
	}

	/**
	 * @see ::render
	 */
	public function __toString(): string
	{
		return $this->render();
	}
}
