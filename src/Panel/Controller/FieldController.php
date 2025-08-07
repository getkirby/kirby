<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\Find;
use Kirby\Form\Field;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Http\Router;
use Kirby\Panel\Area;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
trait FieldController
{
	public function __construct(
		public Field|FieldClass $field,
		public string|null $path = null
	) {
	}

	protected function area(): Area
	{
		return new Area(id: 'site');
	}

	public static function factory(
		string $model,
		string $filename,
		string $field,
		string|null $path = null
	) {
		// for page/user/site field dialogs
		if ($path === null) {
			$model = Find::parent($model);

			return new static(
				field: Form::for($model)->field($filename),
				path: $field
			);
		}

		// for file field dialogs
		$model = Find::file($model, $filename);

		return new static(
			field: Form::for($model)->field($field),
			path: $path
		);
	}

	public function load(): mixed
	{
		return Router::execute($this->path, 'GET', $this->routes());
	}

	public function submit(): mixed
	{
		return Router::execute($this->path, 'POST', $this->routes());
	}
}
