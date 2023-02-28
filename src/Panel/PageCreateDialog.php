<?php

namespace Kirby\Panel;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class PageCreateDialog
{
	protected Page|Site $parent;
	protected string $parentId;
	protected string|null $template;

	public function __construct(
		string|null $parentId,
		string|null $template,
	) {
		// if ($template === null) {
		// 	throw new InvalidArgumentException('Please provide a template');
		// }

		$this->parentId = $parentId ?? 'site';
		$this->parent   = Find::parent($this->parentId);
		$this->template = $template;
	}

	/**
	 * Get an array of all blueprints for the parent view
	 */
	public function blueprints(Site|Page $view, string $sectionId): array
	{
		return array_map(function ($blueprint) {
			$blueprint['name'] ??= $blueprint['value'] ?? null;
			return $blueprint;
		}, $view->blueprints($sectionId));
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
	 * Loads custom fields for the page type
	 */
	public function customFields(): array
	{
		// create a temporary page object
		$page = Page::factory([
			'slug'     => 'new',
			'template' => $this->template,
			'model'    => $this->template
		]);

		$custom    = [];
		$ignore    = ['title', 'slug', 'parent', 'template'];
		$blueprint = $page->blueprint();
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
	 * Loads all the fields for the dialog
	 */
	public function fields(): array
	{
		return [
			'title' => Field::title([
				'required'  => true,
				'preselect' => true
			]),
			'slug' => Field::slug([
				'required' => true,
				'sync'     => 'title',
				'path'     => $this->parent instanceof Page ? '/' . $this->parent->id() . '/' : '/'
			]),
			'parent'   => Field::hidden(),
			'template' => Field::hidden()
		];
	}

	/**
	 * Provides all the props for the
	 * dialog, including the fields and
	 * initial values
	 */
	public function load(
		string|null $viewId,
		string|null $sectionId
	): array {
		if (empty($this->template) === true) {
			$view       = $this->view($viewId);
			$blueprints = $this->blueprints($view, $sectionId);

			// there are multiple blueprints to choose from.
			// show the picker dialog first
			if (count($blueprints) > 1) {
				$picker = new PageTemplateDialog($this->parentId);
				return $picker->load($blueprints);
			}

			$this->template = $blueprints[0]['name'];
		}

		return [
			'component' => 'k-form-dialog',
			'props' => [
				'fields'       => array_merge($this->fields(), $this->customFields()),
				'submitButton' => I18n::translate('page.draft.create'),
				'value'        => $this->value()
			]
		];
	}

	/**
	 * Prepares and cleans up the input data
	 */
	public function sanitize(array $input): array
	{
		$content = [
			'title' => trim($input['title'] ?? ''),
		];

		foreach ($this->customFields() as $name => $field) {
			$content[$name] = $input[$name] ?? null;
		}

		return [
			'content'  => $content,
			'slug'     => $input['slug'] ?? null,
			'template' => $this->template,
		];
	}

	/**
	 * Submits the dialog form and creates the new page
	 */
	public function submit(array $input): array
	{
		if (empty($input['template']) === true) {
			$picker = new PageTemplateDialog($this->parentId);
			return $picker->submit($input);
		}

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
			'slug'     => '',
			'template' => $this->template,
			'title'    => '',
		];
	}

	public function view(string|null $viewId): Site|Page
	{
		return Find::parent($viewId ?? $this->parentId);
	}
}
