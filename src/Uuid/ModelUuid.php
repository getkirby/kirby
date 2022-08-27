<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Throwable;

/**
 * Base for Uuids for models where id string
 * is stored in the content, such as pages and files
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
	 * Look up Uuid in local and global index
	 * and return the identifiable model object.
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
	 * Returns the UUID's id string. If not set yet,
	 * creates a neq unique ID and writes it to content file
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
		$kirby = App::instance();
		$user  = $kirby->auth()->currentUserFromImpersonation();
		$kirby->impersonate('kirby');
		try {
			$this->model = $this->model->update(['uuid' => $id]);

			// @codeCoverageIgnoreStart
		} catch (Throwable $e) {
			// TODO: needs probably a better solution
			if ($e->getMessage() !== 'The directory "/dev/null" cannot be created') {
				throw $e;
			}
		}
		// @codeCoverageIgnoreEnd
		$kirby->impersonate($user);

		// TODO: replace the above in 3.9.0 with
		// App::instance()->impersonate(
		// 	'kirby',
		// 	fn () => $this->model = $this->model->update(['uuid' => $id])
		// );

		// update the Uri object
		$this->uri->host($id);

		return $id;
	}

	/**
	 * Retrieves the existing ID string for the model
	 * or generates a new one, if required
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
		$site = App::instance()->site()->url();
		return $site . '/@/' . static::TYPE . '/' . $this->id();
	}
}
