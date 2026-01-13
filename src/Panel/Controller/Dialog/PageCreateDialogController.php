<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Blueprint\Section;
use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\PageRules;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Content\MemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\A;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

/**
 * Controls the Panel dialog to create a new page
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageCreateDialogController extends ModelCreateDialogController
{
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

	public array $blueprints;

	/**
	 * @var \Kirby\Cms\Page
	 */
	public ModelWithContent $model;

	/**
	 * @var \Kirby\Cms\Page|\Kirby\Cms\Site
	 */
	public Page|Site|User $parent;

	public function __construct(
		Page|Site|null $parent = null,
		Section|string|null $section = null
	) {
		parent::__construct(parent: $parent);

		// convert section name to section object
		if (is_string($section) === true) {
			$section = $parent->blueprint()->section($section);
		}

		// gather all available blueprints from section or parent
		$this->blueprints = A::map(
			$section?->blueprints() ?? $this->parent->blueprints(),
			function ($blueprint) {
				$blueprint['name'] ??= $blueprint['value'] ?? null;
				return $blueprint;
			}
		);
	}

	/**
	 * Get an array of all available blueprints
	 */
	public function blueprints(): array
	{

		return $this->blueprints;
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
				message: 'Page create dialog: title and slug must not be false at the same time'
			);
		}

		// title field
		if ($title === null || is_array($title) === true) {
			$label = $title['label'] ?? 'title';
			$fields['title'] = Field::title([
				...$title ?? [],
				'label'     => $this->i18n($label),
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
			'parent'   => Field::hidden(), // @deprecated
			'section'  => Field::hidden(), // @deprecated
			'template' => Field::hidden(),
			'view'     => Field::hidden(), // @deprecated
		];
	}

	protected function customFieldsIgnore(): array
	{
		return [...parent::customFieldsIgnore(), 'title', 'slug'];
	}

	/**
	 * @deprecated 6.0.0
	 */
	public static function factory(): static
	{
		$kirby   = App::instance();
		$request = $kirby->request();
		$view    = $request->get('view');
		$parent  = $view ? Find::parent($view) : $kirby->site();
		$section = $request->get('section');

		return new static(
			parent:  $parent,
			section: $section
		);
	}
	/**
	 * Provides all the props for the
	 * dialog, including the fields and
	 * initial values
	 */
	public function load(): Dialog
	{
		$blueprints = $this->blueprints();

		$status   = $this->blueprint()->create()['status'] ?? 'draft';
		$status   = $this->blueprint()->status()[$status]['label'] ?? null;
		$status ??= $this->i18n('page.status.' . $status);

		$value   = $this->value();
		$fields  = $this->fields();
		$visible = array_filter(
			$fields,
			fn ($field) => ($field['hidden'] ?? null) !== true
		);

		// immediately submit the dialog if there is no editable field
		if ($visible === [] && count($blueprints) < 2) {
			$response = $this->submit($value);

			Panel::go(
				url: $response['redirect'] ?? $this->parent->panel()->url(true)
			);
		}

		return new FormDialog(
			component:    'k-page-create-dialog',
			blueprints:   $blueprints,
			fields:       $fields,
			submitButton: $this->i18n('page.create', ['status' => $status]),
			template:     $this->template(),
			value:        $value
		);
	}

	/**
	 * Temporary model be created, used to properly render
	 * the blueprint for fields
	 */
	public function model(): Page
	{
		if (isset($this->model) === true) {
			return $this->model;
		}

		$props = [
			'slug'     => $this->request->get('slug', '__temp__'),
			'template' => $this->template(),
			'model'    => $this->template(),
			'parent'   => $this->parent instanceof Page ? $this->parent : null,
			'content'  => ['title' => $this->request->get('title')]
		];

		// make sure that a UUID gets generated
		// and added to content right away
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] = $this->request->get('uuid', Uuid::generate());
		}

		$this->model = Page::factory($props);

		// change the storage to memory immediately
		// since this is a temporary model
		// so that the model does not write to disk
		$this->model->changeStorage(MemoryStorage::class);

		return $this->model;
	}

	/**
	 * Prepares and cleans up the input data
	 */
	public function sanitize(array $input): array
	{
		$input   = $this->resolveFieldTemplates($input, ['title', 'slug']);
		$content = [
			'title' => trim($input['title'] ?? ''),
			'uuid'  => $input['uuid'] ?? null
		];

		foreach ($this->customFields() as $name => $field) {
			$content[$name] = $input[$name] ?? null;
		}

		// create temporary form to sanitize the input
		// and add default values
		$form = Form::for($this->model())->fill(input: $content);

		return [
			'content'  => $form->strings(true),
			'slug'     => $input['slug'],
			'template' => $this->template()
		];
	}

	/**
	 * Submits the dialog form and creates the new page
	 */
	public function submit(): array
	{
		$input  = $this->sanitize(input: $this->request->get());
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
		$redirect = $this->blueprint()->create()['redirect'] ?? null;

		if ($redirect !== false) {
			$payload['redirect'] = $page->panel()->url(true);
		}

		return $payload;
	}

	public function template(): string
	{
		return $this->request->get('template', $this->blueprints()[0]['name']);
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
		return [
			...parent::value(),
			'parent'   => $this->request->get('parent', ''), // @deprecated
			'section'  => $this->request->get('section', ''), // @deprecated
			'slug'     => $this->request->get('slug', ''),
			'template' => $this->template(),
			'title'    => $this->request->get('title', ''),
			'uuid'     => $this->model()->uuid()->toString(),
			'view'     => $this->request->get('view', ''), // @deprecated
		];
	}
}
