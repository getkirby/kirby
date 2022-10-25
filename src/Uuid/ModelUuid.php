<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Cms\Collection;

/**
 * Base for UUIDs for models where id string
 * is stored in the content, such as pages and files
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelUuid extends Uuid
{
	/**
	 * @var \Kirby\Cms\ModelWithContent|null
	 */
	public Identifiable|null $model;

	public function __construct(
		string|null $uuid = null,
		Identifiable|null $model = null,
		Collection|null $context = null
	) {
		parent::__construct($uuid, $model, $context);

		// ensure that ID gets generated right away if
		// not yet stored any in content file
		$this->id();
	}

	/**
	 * Looks up UUID in local and global index
	 * and returns the identifiable model object
	 */
	protected function findByIndex(): Identifiable|null
	{
		foreach ($this->indexes() as $model) {
			if (static::retrieveId($model) === $this->id()) {
				return $model;
			}
		}

		return null;
	}

	/**
	 * Returns the UUID's id string; if not set yet,
	 * creates a new unique ID and writes it to content file
	 */
	public function id(): string
	{
		if ($id = $this->uri->host()) {
			return $id;
		}

		// generate a new ID (to be saved in the content file)
		$id = static::generate();

		// store the new UUID
		$this->storeId($id);

		// update the Uri object
		$this->uri->host($id);

		return $id;
	}

	/**
	 * Retrieves the ID string (UUID without scheme) for the model
	 * from the content file, if it is already stored there
	 *
	 * @param \Kirby\Cms\ModelWithContent $model
	 */
	public static function retrieveId(Identifiable $model): string|null
	{
		return $model->content('default')->get('uuid')->value();
	}

	/**
	 * Stores the UUID for the model and makes sure
	 * to update the content file and content object cache
	 */
	protected function storeId(string $id): void
	{
		// get the content array from the page
		$data = $this->model->content('default')->toArray();

		// check for an empty content array
		// and read content from file again,
		// just to be sure we don't lose content
		if (empty($data) === true) {
			usleep(1000);
			$data = $this->model->readContent('default');
		}

		// add the UUID to the content array
		if (empty($data['uuid']) === true) {
			$data['uuid'] = $id;
		}

		// overwrite the content in memory for the current request
		if ($this->model->kirby()->multilang() === true) {
			// update the default translation instead of the content object
			// (the default content object is always freshly loaded from the
			// default translation afterwards, so updating the default
			// content object would not have any effect)
			$this->model->translation('default')->update($data);
		} else {
			$this->model->content('default')->update($data);
		}

		// overwrite the content in the file;
		// use the most basic write method to avoid object cloning
		$this->model->writeContent($data, 'default');
	}

	/**
	 * Returns permalink url
	 */
	public function url(): string
	{
		// make sure UUID is cached because the permalink
		// route only looks up UUIDs from cache
		if ($this->isCached() === false) {
			$this->populate();
		}

		return App::instance()->url() . '/@/' . static::TYPE . '/' . $this->id();
	}
}
