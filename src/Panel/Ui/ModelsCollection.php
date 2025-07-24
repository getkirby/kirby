<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Files;
use Kirby\Cms\Pages;
use Kirby\Cms\Users;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
abstract class ModelsCollection extends Collection
{
	public function __construct(
		public Files|Pages|Users $models,
		public array $columns = [],
		public string $component = 'k-collection',
		public array|null $empty = null,
		public string|null $help = null,
		public array|null|bool $image = null,
		public string|null $info = null,
		public string $layout = 'list',
		public array|bool $pagination = false,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'medium',
		public string|null $text = '{{ model.title }}',
		public string|null $theme = null,
	) {
	}

	public function info(): string|null
	{
		return I18n::translate($this->info, $this->info);
	}

	abstract public function item(
		ModelWithContent $model,
		array|null|bool $image,
		string|null $info,
		string $layout,
		string $text,
	): array;

	public function items(): array
	{
		$items = [];

		$image  = $this->image();
		$info   = $this->info();
		$layout = $this->layout();
		$text   = $this->text();

		foreach ($this->models() as $model) {
			$items[] = $this->item(
				model: $model,
				image: $image,
				info: $info,
				layout: $layout,
				text: $text,
			);
		}

		return $items;
	}

	public function models(): Files|Pages|Users
	{
		return $this->models;
	}

	public function pagination(): array|false
	{
		$pagination = $this->models()->pagination();

		if ($pagination === null) {
			return false;
		}

		return [
			'limit'  => $pagination->limit(),
			'offset' => $pagination->offset(),
			'page'   => $pagination->page(),
			'total'  => $pagination->total(),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'pagination' => $this->pagination(),
			'image'      => $this->image(),
			'info'       => $this->info(),
			'text'       => $this->text(),
		];
	}

	public function text(): string
	{
		return I18n::translate($this->text, $this->text);
	}
}
