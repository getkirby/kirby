<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Roles;
use Kirby\Form\Form;
use Kirby\Http\Router;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Provides common field prop definitions
 * for dialogs and other places
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Field
{
	/**
	 * Creates the routes for a field dialog
	 * This is most definitely not a good place for this
	 * method, but as long as the other classes are
	 * not fully refactored, it still feels appropriate
	 */
	public static function dialog(
		ModelWithContent $model,
		string $fieldName,
		string|null $path = null,
		string $method = 'GET',
	) {
		$field  = Form::for($model)->field($fieldName);
		$routes = [];

		foreach ($field->dialogs() as $dialogId => $dialog) {
			$routes = array_merge($routes, Dialog::routes(
				id: $dialogId,
				areaId: 'site',
				options: $dialog
			));
		}

		return Router::execute($path, $method, $routes);
	}

	/**
	 * Creates the routes for a field drawer
	 * This is most definitely not a good place for this
	 * method, but as long as the other classes are
	 * not fully refactored, it still feels appropriate
	 */
	public static function drawer(
		ModelWithContent $model,
		string $fieldName,
		string|null $path = null,
		string $method = 'GET',
	) {
		$field  = Form::for($model)->field($fieldName);
		$routes = [];

		foreach ($field->drawers() as $drawerId => $drawer) {
			$routes = array_merge($routes, Drawer::routes(
				id: $drawerId,
				areaId: 'site',
				options: $drawer
			));
		}

		return Router::execute($path, $method, $routes);
	}

	/**
	 * A standard email field
	 */
	public static function email(array $props = []): array
	{
		return array_merge([
			'label'   => I18n::translate('email'),
			'type'    => 'email',
			'counter' => false,
		], $props);
	}

	/**
	 * File position
	 */
	public static function filePosition(File $file, array $props = []): array
	{
		$index   = 0;
		$options = [];

		foreach ($file->siblings(false)->sorted() as $sibling) {
			$index++;

			$options[] = [
				'value' => $index,
				'text'  => $index
			];

			$options[] = [
				'value'    => $sibling->id(),
				'text'     => $sibling->filename(),
				'disabled' => true
			];
		}

		$index++;

		$options[] = [
			'value' => $index,
			'text'  => $index
		];

		return array_merge([
			'label'   => I18n::translate('file.sort'),
			'type'    => 'select',
			'empty'   => false,
			'options' => $options
		], $props);
	}


	public static function hidden(): array
	{
		return ['hidden' => true];
	}

	/**
	 * Page position
	 */
	public static function pagePosition(Page $page, array $props = []): array
	{
		$index    = 0;
		$options  = [];
		$siblings = $page->parentModel()->children()->listed()->not($page);

		foreach ($siblings as $sibling) {
			$index++;

			$options[] = [
				'value' => $index,
				'text'  => $index
			];

			$options[] = [
				'value'    => $sibling->id(),
				'text'     => $sibling->title()->value(),
				'disabled' => true
			];
		}

		$index++;

		$options[] = [
			'value' => $index,
			'text'  => $index
		];

		// if only one available option,
		// hide field when not in debug mode
		if (count($options) < 2) {
			return static::hidden();
		}

		return array_merge([
			'label'    => I18n::translate('page.changeStatus.position'),
			'type'     => 'select',
			'empty'    => false,
			'options'  => $options,
		], $props);
	}

	/**
	 * A regular password field
	 */
	public static function password(array $props = []): array
	{
		return array_merge([
			'label' => I18n::translate('password'),
			'type'  => 'password'
		], $props);
	}

	/**
	 * User role radio buttons
	 */
	public static function role(
		array $props = [],
		Roles|null $roles = null
	): array {
		$kirby = App::instance();

		// if no $roles where provided, fall back to all roles
		$roles ??= $kirby->roles();

		// exclude the admin role, if the user
		// is not allowed to change role to admin
		$roles = $roles->filter(
			fn ($role) =>
				$role->name() !== 'admin' ||
				$kirby->user()?->isAdmin() === true
		);

		// turn roles into radio field options
		$roles = $roles->values(fn ($role) => [
			'text'  => $role->title(),
			'info'  => $role->description() ?? I18n::translate('role.description.placeholder'),
			'value' => $role->name()
		]);

		return array_merge([
			'label'    => I18n::translate('role'),
			'type'     => count($roles) < 1 ? 'hidden' : 'radio',
			'options'  => $roles
		], $props);
	}

	public static function slug(array $props = []): array
	{
		return array_merge([
			'label' => I18n::translate('slug'),
			'type'  => 'slug',
			'allow' => Str::$defaults['slug']['allowed']
		], $props);
	}

	public static function template(
		array|null $blueprints = [],
		array|null $props = []
	): array {
		$options = [];

		foreach ($blueprints as $blueprint) {
			$options[] = [
				'text'  => $blueprint['title'] ?? $blueprint['text']  ?? null,
				'value' => $blueprint['name']  ?? $blueprint['value'] ?? null,
			];
		}

		return array_merge([
			'label'    => I18n::translate('template'),
			'type'     => 'select',
			'empty'    => false,
			'options'  => $options,
			'icon'     => 'template',
			'disabled' => count($options) <= 1
		], $props);
	}

	public static function title(array $props = []): array
	{
		return array_merge([
			'label' => I18n::translate('title'),
			'type'  => 'text',
			'icon'  => 'title',
		], $props);
	}

	/**
	 * Panel translation select box
	 */
	public static function translation(array $props = []): array
	{
		$translations = [];
		foreach (App::instance()->translations() as $translation) {
			$translations[] = [
				'text'  => $translation->name(),
				'value' => $translation->code()
			];
		}

		return array_merge([
			'label'    => I18n::translate('language'),
			'type'     => 'select',
			'icon'     => 'translate',
			'options'  => $translations,
			'empty'    => false
		], $props);
	}

	public static function username(array $props = []): array
	{
		return array_merge([
			'icon'  => 'user',
			'label' => I18n::translate('name'),
			'type'  => 'text',
		], $props);
	}
}
