<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Toolkit\I18n;

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
		return ['type' => 'hidden'];
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
	public static function role(array $props = []): array
	{
		$kirby   = App::instance();
		$user    = $kirby->user();
		$isAdmin = $user && $user->isAdmin();
		$roles   = [];

		foreach ($kirby->roles() as $role) {
			// exclude the admin role, if the user
			// is not allowed to change role to admin
			if ($role->name() === 'admin' && $isAdmin === false) {
				continue;
			}

			$roles[] = [
				'text'  => $role->title(),
				'info'  => $role->description() ?? I18n::translate('role.description.placeholder'),
				'value' => $role->name()
			];
		}

		return array_merge([
			'label'    => I18n::translate('role'),
			'type'     => count($roles) <= 1 ? 'hidden' : 'radio',
			'options'  => $roles
		], $props);
	}

	public static function slug(array $props = []): array
	{
		return array_merge([
			'label' => I18n::translate('slug'),
			'type'  => 'slug',
		], $props);
	}

	public static function template(array|null $blueprints = [], array|null $props = []): array
	{
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
			'icon'     => 'globe',
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
