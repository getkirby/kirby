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

		// generate ID and write to content file
		$id = static::generate();

		// make sure Kirby has the required permissions
		// for the update action
		$kirby = $this->model->kirby();
		$user  = $kirby->auth()->currentUserFromImpersonation();
		$kirby->impersonate('kirby');

		// get the content array from the page
		$data = $this->model->content()->toArray();

		// check for an empty content array
		// and read content from file again, just to be sure
		// we don't loose content
		if (empty($data) === true) {
			usleep(1000);
			$data = $this->model->readContent();
		}

		// add the UUID to the content array
		if (empty($data['uuid']) === true) {
			$data['uuid'] = $id;
		}

		// update the content object
		$this->model->content()->update($data);

		// use the most basic write method to avoid object cloning
		$this->model->writeContent($data);

		$kirby->impersonate($user);

		// TODO: replace the above in 3.9.0 with
		// App::instance()->impersonate(
		// 	'kirby',
		// 	fn () => $this->model = $this->model->writeContent($data)
		// );

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
		return $model->content()->get('uuid')->value();
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
