<?php

namespace Kirby\Panel;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class PageCreateDialog
{
	protected PageBlueprint $blueprint;
	protected Page|Site $parent;
	protected string $parentId;
	protected string|null $sectionId;
	protected string|null $slug;
	protected string|null $template;
	protected string|null $title;
	protected Page|Site $view;
	protected string|null $viewId;

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
		$page = Page::factory([
			'slug'     => 'new',
			'template' => $this->template,
			'model'    => $this->template
		]);

		return $this->blueprint ??= $page->blueprint();
	}

	/**
	 * Get an array of all blueprints for the parent view
	 */
	public function blueprints(): array
	{
		return array_map(function ($blueprint) {
			$blueprint['name'] ??= $blueprint['value'] ?? null;
			return $blueprint;
		}, $this->view->blueprints($this->sectionId));
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
		$types     = $this->customFieldTypes();

		foreach ($blueprint->create()['fields'] ?? [] as $name) {
			if (!$field = ($fields[$name] ?? null)) {
				continue;
			}

			if (in_array($field['type'], $types) === false) {
				continue;
			}

			if (in_array($name, $ignore) === true) {
				continue;
			}

			// switch all fields to 1/1
			$field['width'] = '1/1';

			// add the field to the form
			$custom[$name] = $field;
		}

		return $custom;
	}

	/**
	 * A list of supported custom field types
	 */
	public function customFieldTypes(): array
	{
		return [
			'checkboxes',
			'date',
			'email',
			'multiselect',
			'number',
			'list',
			'radio',
			'select',
			'tags',
			'tel',
			'text',
			'textarea',
			'toggles',
			'time',
			'url',
			'writer',
		];
	}

	/**
	 * Loads all the fields for the dialog
	 */
	public function fields(): array
	{
		return [
			'title' => Field::title([
				'label'     => $this->blueprint()->create()['title']['label'] ?? I18n::translate('title'),
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
	 * Provides all the props for the
	 * dialog, including the fields and
	 * initial values
	 */
	public function load(): array
	{
		$blueprints = $this->blueprints();

		$this->template ??= $blueprints[0]['name'];

		return [
			'component' => 'k-page-create-dialog',
			'props' => [
				'blueprints'   => $blueprints,
				'fields'       => array_merge($this->fields(), $this->customFields()),
				'submitButton' => I18n::translate('page.draft.create'),
				'template'     => $this->template,
				'value'        => $this->value()
			]
		];
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
		$input = $this->sanitize($input);

		$this->validate($input);

		$page = $this->parent->createChild($input);

		return [
			'event'    => 'page.create',
			'redirect' => $page->panel()->url(true)
		];
	}

	public function validate(array $input): bool
	{
		if (Str::length($input['content']['title']) === 0) {
			throw new InvalidArgumentException([
				'key' => 'page.changeTitle.empty'
			]);
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
