<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;
use Kirby\Form\Field;
use Kirby\Form\Form;
use Kirby\Http\Router;
use Kirby\Panel\Dialog;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class FieldDialog
{
	public function __construct(
		public Field $field,
		public string|null $path = null
	) {
	}

	public static function forModel(
		string $model,
		string $field,
		string|null $path = null
	): static {
		$model = Find::parent($model);
		$field = Form::for($model)->field($field);
		return new static($field, $path);
	}

	public static function forFile(
		string $model,
		string $filename,
		string $field,
		string|null $path = null
	): static {
		$file  = Find::file($model, $filename);
		$field = Form::for($file)->field($field);
		return new static($field, $path);
	}

	public function render(): array
	{
		return Router::execute($this->path, 'GET', $this->routes());
	}

	public function routes(): array
	{
		$routes = [];

		foreach ($this->field->dialogs() as $dialogId => $dialog) {
			$routes = [
				...$routes,
				...Dialog::routes(
					id: $dialogId,
					areaId: 'site',
					options: $dialog
				)
			];
		}

		return $routes;
	}

	public function submit(): array
	{
		return Router::execute($this->path, 'POST', $this->routes());
	}
}
