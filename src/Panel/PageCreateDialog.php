<?php

namespace Kirby\Panel;

use Kirby\Cms\File;
use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;
use Kirby\Cms\PageRules;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Content\MemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

/**
 * Manages the Panel dialog to create new pages
 * @since 4.0.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PageCreateDialog
{
	protected PageBlueprint $blueprint;
	protected Page $model;
	protected Page|Site $parent;
	protected string $parentId;
	protected string|null $sectionId;
	protected string|null $slug;
	protected string|null $template;
	protected string|null $title;
	protected string|null $uuid;
	protected Page|Site|User|File $view;
	protected string|null $viewId;

	public static array $fieldTypes = [
		'checkboxes',
		'date',
		'email',
		'info',
		'line',
		'link',
		'list',
		'number',
		'multiselect',
		'radio',
		'range',
		'select',
		'slug',
		'tags',
		'tel',
		'text',
		'toggle',
		'toggles',
		'time',
		'url'
	];

	public function __construct(
		string|null $parentId,
		string|null $sectionId,
		string|null $template,
		string|null $viewId,

		// optional
		string|null $slug = null,
		string|null $title = null,
		string|null $uuid = null,
	) {
		$this->parentId  = $parentId ?? 'site';
		$this->parent    = Find::parent($this->parentId);
		$this->sectionId = $sectionId;
		$this->slug      = $slug;
		$this->template  = $template;
		$this->title     = $title;
		$this->uuid      = $uuid;
		$this->viewId    = $viewId;
		$this->view      = Find::parent($this->viewId ?? $this->parentId);
	}

	/**
	 * Get the blueprint settings for the new page
	 */
	public function blueprint(): PageBlueprint
	{
		// create a temporary page object
		return $this->blueprint ??= $this->model()->blueprint();
	}

	/**
	 * Get an array of all blueprints for the parent view
	 */
	public function blueprints(): array
	{
		return A::map(
			$this->view->blueprints($this->sectionId),
			function ($blueprint) {
				$blueprint['name'] ??= $blueprint['value'] ?? null;
				return $blueprint;
			}
		);
	}

	/**
	 * All the default fields for the dialog
	 */
	public function coreFields(): array
	{
		$fields = [];

		$title = $this->blueprint()->create()['title'] ?? null;
		$slug  = $this->blueprint()->create()['slug'] ?? null;

		if ($title === false || $slug === false) {
			throw new InvalidArgumentException(
				message: 'Page create dialog: title and slug must not be false'
			);
		}

		// title field
		if ($title === null || is_array($title) === true) {
			$label = $title['label'] ?? 'title';
			$fields['title'] = Field::title([
				...$title ?? [],
				'label'     => I18n::translate($label, $label),
				'required'  => true,
				'preselect' => true
			]);
		}

		// slug field
		if ($slug === null) {
			$fields['slug'] = Field::slug([
				'required' => true,
				'sync'     => 'title',
				'path'     => $this->parent instanceof Page ? '/' . $this->parent->id() . '/' : '/'
			]);
		}

		// pass uuid field to the dialog if uuids are enabled
		// to use the same uuid and prevent generating a new one
		// when the page is created
		if (Uuids::enabled() === true) {
			$fields['uuid'] = Field::hidden();
		}

		return [
			...$fields,
			'parent'   => Field::hidden(),
			'section'  => Field::hidden(),
			'template' => Field::hidden(),
			'view'     => Field::hidden(),
		];
	}

	/**
	 * Loads custom fields for the page type
	 */
	public function customFields(): array
	{
		$custom    = [];
		$ignore    = ['title', 'slug', 'parent', 'template', 'uuid'];
		$blueprint = $this->blueprint();
		$fields    = $blueprint->fields();

		foreach ($blueprint->create()['fields'] ?? [] as $name) {
			if (!$field = ($fields[$name] ?? null)) {
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
			model: $this->model()
		);

		return $form->fields()->toProps();
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
	 * Provides all the props for the
	 * dialog, including the fields and
	 * initial values
	 */
	public function load(): array
	{
		$blueprints = $this->blueprints();

		$this->template ??= $blueprints[0]['name'];

		$status   = $this->blueprint()->create()['status'] ?? 'draft';
		$status   = $this->blueprint()->status()[$status]['label'] ?? null;
		$status ??= I18n::translate('page.status.' . $status);

		$fields  = $this->fields();
		$visible = array_filter(
			$fields,
			fn ($field) => ($field['hidden'] ?? null) !== true
		);

		// immediately submit the dialog if there is no editable field
		if ($visible === [] && count($blueprints) < 2) {
			$input    = $this->value();
			$response = $this->submit($input);
			$response['redirect'] ??= $this->parent->panel()->url(true);
			Panel::go($response['redirect']);
		}

		return [
			'component' => 'k-page-create-dialog',
			'props' => [
				'blueprints'   => $blueprints,
				'fields'       => $fields,
				'submitButton' => I18n::template('page.create', [
					'status' => $status
				]),
				'template'     => $this->template,
				'value'        => $this->value()
			]
		];
	}

	/**
	 * Temporary model for the page to
	 * be created, used to properly render
	 * the blueprint for fields
	 */
	public function model(): Page
	{
		if (isset($this->model) === true) {
			return $this->model;
		}

		$props = [
			'slug'     => $this->slug ?? '__new__',
			'template' => $this->template,
			'model'    => $this->template,
			'parent'   => $this->parent instanceof Page ? $this->parent : null,
			'content'  => ['title' => $this->title],
		];

		// make sure that a UUID gets generated
		// and added to content right away
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] = $this->uuid = Uuid::generate();
		}

		$this->model = Page::factory($props);

		// change the storage to memory immediately
		// since this is a temporary model
		// so that the model does not write to disk
		$this->model->changeStorage(MemoryStorage::class);

		return $this->model;
	}

	/**
	 * Generates values for title and slug
	 * from template strings from the blueprint
	 */
	public function resolveFieldTemplates(array $input): array
	{
		$title = $this->blueprint()->create()['title'] ?? null;
		$slug  = $this->blueprint()->create()['slug'] ?? null;

		// create temporary page object
		// to resolve the template strings
		$page = $this->model()->clone(['content' => $input]);

		if (is_string($title)) {
			$input['title'] = $page->toSafeString($title);
		}

		if (is_string($slug)) {
			$input['slug'] = $page->toSafeString($slug);
		}

		return $input;
	}

	/**
	 * Prepares and cleans up the input data
	 */
	public function sanitize(array $input): array
	{
		$input['title'] ??= $this->title ?? '';
		$input['slug']  ??= $this->slug  ?? '';
		$input['uuid']  ??= $this->uuid  ?? null;

		$input   = $this->resolveFieldTemplates($input);
		$content = ['title' => trim($input['title'])];

		if ($uuid = $input['uuid'] ?? null) {
			$content['uuid'] = $uuid;
		}

		foreach ($this->customFields() as $name => $field) {
			$content[$name] = $input[$name] ?? null;
		}

		// create temporary form to sanitize the input
		// and add default values
		$form = Form::for($this->model())->fill(input: $content);

		return [
			'content'  => $form->strings(true),
			'slug'     => $input['slug'],
			'template' => $this->template,
		];
	}

	/**
	 * Submits the dialog form and creates the new page
	 */
	public function submit(array $input): array
	{
		$input  = $this->sanitize($input);
		$status = $this->blueprint()->create()['status'] ?? 'draft';

		// validate the input before creating the page
		$this->validate($input, $status);

		$page = $this->parent->createChild($input);

		if ($status !== 'draft') {
			// grant all permissions as the status is set in the blueprint and
			// should not be treated as if the user would try to change it
			$page->kirby()->impersonate(
				'kirby',
				fn () => $page->changeStatus($status)
			);
		}

		$payload = [
			'event' => 'page.create'
		];

		// add redirect, if not explicitly disabled
		if (($this->blueprint()->create()['redirect'] ?? null) !== false) {
			$payload['redirect'] = $page->panel()->url(true);
		}

		return $payload;
	}

	public function validate(array $input, string $status = 'draft'): bool
	{
		// basic validation
		PageRules::validateTitleLength($input['content']['title']);
		PageRules::validateSlugLength($input['slug']);

		// if the page is supposed to be published directly,
		// ensure that all field validations are met
		if ($status !== 'draft') {
			// create temporary form to validate the input
			$form = Form::for($this->model())->fill(input: $input['content']);

			if ($form->isInvalid() === true) {
				throw new InvalidArgumentException(
					key: 'page.changeStatus.incomplete'
				);
			}
		}

		return true;
	}

	public function value(): array
	{
		$value = [
			'parent'   => $this->parentId,
			'section'  => $this->sectionId,
			'slug'     => $this->slug ?? '',
			'template' => $this->template,
			'title'    => $this->title ?? '',
			'uuid'     => $this->uuid,
			'view'     => $this->viewId,
		];

		// add default values for custom fields
		foreach ($this->customFields() as $name => $field) {
			if ($default = $field['default'] ?? null) {
				$value[$name] = $default;
			}
		}

		return $value;
	}
}
