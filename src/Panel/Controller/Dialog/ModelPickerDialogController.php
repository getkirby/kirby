<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Collector\ModelsCollector;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\Str;

/**
 * Controls a Panel dialog for selecting
 * models (pages, files, users)
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class ModelPickerDialogController extends DialogController
{
	protected const string TYPE = 'model';

	public int $page = 1;
	public string|null $search = null;

	public function __construct(
		public ModelWithContent $model,
		public bool $hasSearch = true,
		public array|null $image = [],
		public string|null $info = null,
		public string $layout = 'list',
		public int|null $max = null,
		public bool $multiple = true,
		public string|null $size = null,
		public string|null $text = null,
	) {
		parent::__construct();

		$this->page   = $this->request->get('page', 1);
		$this->search = $this->request->get('search');
	}

	/**
	 * Returns the collector to retrieve the
	 * queried, searched, sorted and paginated models
	 */
	abstract public function collector(): ModelsCollector;

	/**
	 * Finds a model by its ID
	 */
	abstract public function find(string $id): ModelWithContent|null;

	/**
	 * Returns the picker data for a model
	 */
	public function item(ModelWithContent $model): array
	{
		return $model->panel()->pickerData([
			'image'  => $this->image,
			'info'   => $this->info,
			'layout' => $this->layout,
			'text'   => $this->text,
		]);
	}

	/**
	 * Fetches all items for the picker
	 */
	public function items(): array
	{
		return $this->collector()->models(paginated: true)->values(
			fn (ModelWithContent $model) => $this->item($model)
		);
	}

	/**
	 * Load the picker dialog
	 */
	public function load(): Dialog
	{
		return new Dialog(...$this->props());
	}

	/**
	 * Returns the props for the dialog
	 */
	public function props(): array
	{
		return [
			'component'  => 'k-' . static::TYPE . '-picker-dialog',
			'hasSearch'  => $this->hasSearch,
			'items'      => $this->items(),
			'layout'     => $this->layout,
			'max'        => $this->max,
			'multiple'   => $this->multiple,
			'pagination' => $this->collector()->pagination()->toArray(),
			'size'       => $this->size,
			'value'      => Str::split($this->request->get('value', ''))
		];
	}
}
