<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Roles;
use Kirby\Form\Field\EmailField;
use Kirby\Form\Field\HiddenField;
use Kirby\Form\Field\PasswordField;
use Kirby\Form\Field\SlugField;
use Kirby\Panel\Form\Field\FilePositionField;
use Kirby\Panel\Form\Field\PagePositionField;
use Kirby\Panel\Form\Field\RoleField;
use Kirby\Panel\Form\Field\TemplateField;
use Kirby\Panel\Form\Field\TitleField;
use Kirby\Panel\Form\Field\TranslationField;
use Kirby\Panel\Form\Field\UsernameField;

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
	 * A standard email field
	 */
	public static function email(array $props = []): array
	{
		return EmailField::factory($props)->toArray();
	}

	/**
	 * File position
	 */
	public static function filePosition(File $file, array $props = []): array
	{
		$field = new FilePositionField(...[
			'file'     => $file,
			'required' => true,
			...$props
		]);

		return $field->toArray();
	}

	public static function hidden(array $props = []): array
	{
		return HiddenField::factory($props)->toArray();
	}

	/**
	 * Page position
	 */
	public static function pagePosition(Page $page, array $props = []): array
	{
		$field = new PagePositionField(...[
			'page'     => $page,
			'required' => true,
			...$props
		]);

		// hide filed when there is only one available option
		if (count($field->options()) < 2) {
			return static::hidden($props);
		}

		return $field->toArray();
	}

	/**
	 * A regular password field
	 */
	public static function password(array $props = []): array
	{
		return PasswordField::factory($props)->toArray();
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

		$field = new RoleField(...[
			'roles' => $roles,
			...$props
		]);

		if (count($field->options()) <= 1) {
			return static::hidden([
				'name' => 'role',
				...$props
			]);
		}

		return $field->toArray();
	}

	public static function slug(array $props = []): array
	{
		return SlugField::factory($props)->toArray();
	}

	public static function template(
		array|null $blueprints = [],
		array|null $props = []
	): array {
		return (new TemplateField(...[
			'blueprints' => $blueprints,
			...$props,
		]))->toArray();
	}

	public static function title(array $props = []): array
	{
		return TitleField::factory($props)->toArray();
	}

	/**
	 * Panel translation select box
	 */
	public static function translation(array $props = []): array
	{
		return TranslationField::factory($props)->toArray();
	}

	public static function username(array $props = []): array
	{
		return UsernameField::factory($props)->toArray();
	}
}
