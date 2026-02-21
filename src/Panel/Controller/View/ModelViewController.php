<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Fields;
use Kirby\Http\Uri;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Model;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;

/**
 * Controls a model view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelViewController extends ViewController
{
	protected Model $panel;

	public function __construct(
		protected ModelWithContent $model
	) {
		parent::__construct();
		$this->panel = $this->model->panel();
	}

	public function breadcrumb(): array
	{
		return [];
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view($this);
	}

	public function component(): string
	{
		return 'k-' . ($this->model::CLASS_ALIAS ?? 'model') . '-view';
	}

	public function load(): View
	{
		return new View(...$this->props());
	}

	public function model(): ModelWithContent
	{
		return $this->model;
	}

	public function next(): array|null
	{
		return null;
	}

	public function prev(): array|null
	{
		return null;
	}

	/**
	 * Returns link url and title for optional sibling model
	 * and preserves tab selection
	 */
	protected static function prevNext(
		ModelWithContent|null $model = null,
		string $title = 'title'
	): array|null {
		if ($model === null) {
			return null;
		}

		$data = $model->panel()->toLink($title);

		if ($tab = $model->kirby()->request()->get('tab')) {
			$uri = new Uri($data['link'], [
				'query' => ['tab' => $tab]
			]);

			$data['link'] = $uri->toString();
		}

		return $data;
	}

	public function props(): array
	{
		$versions = $this->versions();

		$props = [
			'component'   => $this->component(),
			'api'         => $this->panel->url(true),
			'breadcrumb'  => $this->breadcrumb(),
			'buttons'     => $this->buttons(),
			'id'          => $this->model->id(),
			'link'        => $this->panel->url(true),
			'lock'        => $this->model->lock()->toArray(),
			'next'        => $this->next(...),
			'permissions' => $this->model->permissions()->toArray(),
			'prev'        => $this->prev(...),
			'tabs'        => $this->tabs(),
			'title'       => $this->title(),
			'uuid'        => $this->model->uuid()?->toString(),
			'versions'    => [
				'latest'  => (object)$versions['latest'],
				'changes' => (object)$versions['changes']
			]
		];

		// only send the tab if it exists
		// this will let the vue component define
		// a proper default value
		if ($tab = $this->tab()) {
			$props['tab'] = $tab;
		}

		return $props;
	}

	public function tab(): array|null
	{
		$tab   = $this->request->get('tab');
		$tab   = $this->model->blueprint()->tab($tab);
		$tab ??= $this->tabs()[0] ?? null;
		return $tab;
	}

	public function tabs(): array
	{
		return $this->model->blueprint()->tabs();
	}

	abstract public function title(): string;

	/**
	 * Creates an array with two versions of the content:
	 * `latest` and `changes`.
	 *
	 * The content is passed through the Fields class
	 * to ensure that the content is in the correct format
	 * for the Panel. If there's no `changes` version, the `latest`
	 * version is used for both.
	 */
	public function versions(): array
	{
		$language = Language::ensure('current');
		$fields   = Fields::for($this->model, $language);

		$latestVersion  = $this->model->version('latest');
		$changesVersion = $this->model->version('changes');

		$latestContent  = $latestVersion->content($language)->toArray();
		$changesContent = $latestContent;

		if ($changesVersion->exists($language) === true) {
			$changesContent = $changesVersion->content($language)->toArray();
		}

		return [
			'latest'  => $fields->reset()->fill($latestContent)->toFormValues(),
			'changes' => $fields->reset()->fill($changesContent)->toFormValues()
		];
	}
}
