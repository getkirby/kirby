<?php

namespace Kirby\Uuid;

use Closure;
use Hidehalo\Nanoid\Client as Nanoid;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Throwable;

/**
 * Handles the unique ID string of UUIDs
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Id
{
	/**
	 * Customisable callback function for generating new ID strings
	 * instead of using Nanoid. Receives length of string as parameter.
	 */
	public static Closure|null $generator = null;

	/**
	 * Generate a new ID string
	 */
	public static function generate(int $length = 15): string
	{
		if (static::$generator !== null) {
			return (static::$generator)($length);
		}

		return (new Nanoid())->generateId($length);
	}

	/**
	 * Retrieves the ID string from model
	 */
	public static function get(Identifiable|null $model): string|null
	{
		return match (true) {
			$model instanceof Site
				=> '',

			$model instanceof User,
			// @codeCoverageIgnoreStart
			$model instanceof Block
			// @codeCoverageIgnoreEnd
				=> $model->id(),

			$model instanceof Page,
			$model instanceof File,
			// @codeCoverageIgnoreStart
			$model instanceof StructureObject
			// @codeCoverageIgnoreEnd
				=> $model->content()->get('uuid')->value()
		};
	}

	/**
	 * Update content file with generated ID
	 */
	public static function write(
		ModelWithContent $model,
		string $id
	): ModelWithContent {
		// make sure Kirby has the required permissions
		// for the update action
		$kirby = App::instance();
		$user  = $kirby->auth()->currentUserFromImpersonation();
		$kirby->impersonate('kirby');
		try {
			$model = $model->update(['uuid' => $id]);

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

		return $model;
	}
}
