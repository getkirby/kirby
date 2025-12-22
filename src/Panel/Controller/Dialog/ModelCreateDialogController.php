<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Blueprint\Blueprint;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Panel\Controller\DialogController;

/**
 * Controls a Panel dialog to create a new model
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelCreateDialogController extends DialogController
{
	public static array $fieldTypes = [];

	protected Blueprint $blueprint;
	public ModelWithContent $model;
	public Page|Site|User $parent;

	public function __construct(
		Page|Site|User|null $parent = null
	) {
		parent::__construct();

		$this->parent = $parent ?? $this->site;
	}

	/**
	 * Get the blueprint for the new page
	 */
	public function blueprint(): Blueprint
	{
		return $this->blueprint ??= $this->model()->blueprint();
	}

	/**
	 * All the default fields for the dialog
	 */
	abstract public function coreFields(): array;

	/**
	 * Loads custom fields for the page type
	 */
	public function customFields(): array
	{
		$custom = [];
		$fields = $this->blueprint()->fields();
		$ignore = $this->customFieldsIgnore();

		foreach ($this->blueprint()->create()['fields'] ?? [] as $name) {
			$field = $fields[$name] ?? null;

			if ($field === null) {
				throw new InvalidArgumentException(
					message: 'Unknown field  "' . $name . '" in create dialog'
				);
			}

			if (in_array($field['type'], static::$fieldTypes, true) === false) {
				throw new InvalidArgumentException(
					message: 'Field type "' . $field['type'] . '" not supported in create dialog'
				);
			}

			if (in_array($name, $ignore, true) === true) {
				throw new InvalidArgumentException(
					message: 'Field name "' . $name . '" not allowed as custom field in create dialog'
				);
			}

			// switch all fields to 1/1
			$field['width'] = '1/1';

			// add the field to the form
			$custom[$name] = $field;
		}

		// create form so that field props, options etc.
		// can be properly resolved
		$form = new Form(
			fields: $custom,
			model:  $this->model()
		);

		return $form->fields()->toProps(defaults: true);
	}

	protected function customFieldsIgnore(): array
	{
		return array_keys($this->coreFields());
	}

	/**
	 * Loads all the fields for the dialog
	 */
	public function fields(): array
	{
		return [
			...$this->coreFields(),
			...$this->customFields()
		];
	}

	/**
	 * Temporary model to be created,
	 * used to properly render the blueprint for fields
	 */
	abstract public function model(): ModelWithContent;

	/**
	 * Generates values for title and slug
	 * from template strings from the blueprint
	 */
	public function resolveFieldTemplates(array $input, array $fields): array
	{
		// create temporary page object to resolve the template strings
		$page = $this->model()->clone(['content' => $input]);

		foreach ($fields as $field) {
			$template = $this->blueprint()->create()[$field] ?? null;

			if (is_string($template) === true) {
				$input[$field] = $page->toSafeString($template);
			}
		}

		return $input;
	}

	public function value(): array
	{
		$value = [];

		// add default values for custom fields
		foreach ($this->customFields() as $name => $field) {
			$default = $field['default'] ?? null;

			if ($default !== null) {
				$value[$name] = $default;
			}
		}

		return $value;
	}
}
