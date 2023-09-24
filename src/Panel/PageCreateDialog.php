<?php

namespace Kirby\Panel;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;
use Kirby\Cms\PageRules;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

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
	protected Page|Site $view;
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
	) {
		$this->parentId  = $parentId ?? 'site';
		$this->parent    = Find::parent($this->parentId);
		$this->sectionId = $sectionId;
		$this->slug      = $slug;
		$this->template  = $template;
		$this->title     = $title;
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
		$title = $this->blueprint()->create()['title']['label'] ?? 'title';

		return [
			'title' => Field::title([
				'label'     => I18n::translate($title, $title),
				'required'  => true,
				'preselect' => true
			]),
			'slug' => Field::slug([
				'required' => true,
				'sync'     => 'title',
				'path'     => $this->parent instanceof Page ? '/' . $this->parent->id() . '/' : '/'
			]),
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
		$ignore    = ['title', 'slug', 'parent', 'template'];
		$blueprint = $this->blueprint();
		$fields    = $blueprint->fields();

		foreach ($blueprint->create()['fields'] ?? [] as $name) {
			if (!$field = ($fields[$name] ?? null)) {
				throw new InvalidArgumentException('Unknown field  "' . $name . '" in create dialog');
			}

			if (in_array($field['type'], static::$fieldTypes) === false) {
				throw new InvalidArgumentException('Field type "' . $field['type'] . '" not supported in create dialog');
			}

			if (in_array($name, $ignore) === true) {
				throw new InvalidArgumentException('Field name "' . $name . '" not allowed as custom field in create dialog');
			}

			// switch all fields to 1/1
			$field['width'] = '1/1';

			// add the field to the form
			$custom[$name] = $field;
		}

		// create form so that field props, options etc.
		// can be properly resolved
		$form = new Form([
			'fields' => $custom,
			'model'  => $this->model(),
			'strict' => true
		]);

		return $form->fields()->toArray();
	}

	/**
	 * Loads all the fields for the dialog
	 */
	public function fields(): array
	{
		return array_merge(
			$this->coreFields(),
			$this->customFields()
		);
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

		$status = $this->blueprint()->create()['status'] ?? 'draft';
		$status = $this->blueprint()->status()[$status]['label'] ?? I18n::translate('page.status.' . $status);

		return [
			'component' => 'k-page-create-dialog',
			'props' => [
				'blueprints'   => $blueprints,
				'fields'       => $this->fields(),
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
		return $this->model ??= Page::factory([
			'slug'     => 'new',
			'template' => $this->template,
			'model'    => $this->template,
			'parent'   => $this->parent instanceof Page ? $this->parent : null
		]);
	}

	/**
	 * Prepares and cleans up the input data
	 */
	public function sanitize(array $input): array
	{
		$input['slug']  ??= $this->slug  ?? '';
		$input['title'] ??= $this->title ?? '';

		$content = [
			'title' => trim($input['title']),
		];

		foreach ($this->customFields() as $name => $field) {
			$content[$name] = $input[$name] ?? null;
		}

		return [
			'content'  => $content,
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
			$form = Form::for($this->model(), ['values' => $input['content']]);

			if ($form->isInvalid() === true) {
				throw new InvalidArgumentException([
					'key' => 'page.changeStatus.incomplete'
				]);
			}
		}

		return true;
	}

	public function value(): array
	{
		return [
			'parent'   => $this->parentId,
			'section'  => $this->sectionId,
			'slug'     => $this->slug ?? '',
			'template' => $this->template,
			'title'    => $this->title ?? '',
			'view'     => $this->viewId,
		];
	}
}
