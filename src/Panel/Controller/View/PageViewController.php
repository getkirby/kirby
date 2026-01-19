<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Panel\Model;
use Kirby\Panel\Ui\Button\ViewButtons;

/**
 * Controls the page view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageViewController extends ModelViewController
{
	/**
	 * @var \Kirby\Cms\Page $model
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\Page
	 */
	protected Model $panel;

	public function __construct(
		Page $model
	) {
		parent::__construct($model);
	}

	public function breadcrumb(): array
	{
		$parents = $this->model->parents()->flip()->merge($this->model);

		return $parents->values(
			fn ($parent) => [
				'label' => $parent->title()->toString(),
				'link'  => $parent->panel()->url(true),
			]
		);
	}

	public function buttons(): ViewButtons
	{
		return parent::buttons()->defaults(
			'open',
			'preview',
			'-',
			'settings',
			'languages',
			'status'
		);
	}

	public static function factory(string $id): static
	{
		return new static(model: Find::page($id));
	}

	public function next(): array|null
	{
		return static::prevNext($this->siblings('next')->first());
	}

	public function prev(): array|null
	{
		return static::prevNext($this->siblings('prev')->last());
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'blueprint' => $this->model->intendedTemplate()->name(),
		];
	}

	public function siblings(string $direction): Pages
	{
		$navigation = $this->model->blueprint()->navigation();
		$sortBy     = $navigation['sortBy'] ?? null;
		$status     = $navigation['status'] ?? null;
		$template   = $navigation['template'] ?? null;
		$direction  = $direction === 'prev' ? 'prev' : 'next';

		// if status is defined in navigation,
		// all items in the collection are used
		// (drafts, listed and unlisted) otherwise
		// it depends on the status of the page
		$siblings = match ($status) {
			null    => $this->model->siblings(),
			default => $this->model->parentModel()->childrenAndDrafts()
		};

		// sort the collection if custom sortBy
		// defined in navigation otherwise
		// default sorting will apply
		if ($sortBy !== null) {
			$siblings = $siblings->sort(...$siblings::sortArgs($sortBy));
		}

		$siblings = $this->model->{$direction . 'All'}($siblings);

		if (empty($navigation) === false) {
			$statuses  = (array)($status ?? $this->model->status());
			$templates = (array)($template ?? $this->model->intendedTemplate());

			// do not filter if template navigation is all
			if (in_array('all', $templates, true) === false) {
				$siblings = $siblings->filter('intendedTemplate', 'in', $templates);
			}

			// do not filter if status navigation is all
			if (in_array('all', $statuses, true) === false) {
				$siblings = $siblings->filter('status', 'in', $statuses);
			}
		} else {
			$siblings = $siblings
				->filter('intendedTemplate', $this->model->intendedTemplate())
				->filter('status', $this->model->status());
		}

		return $siblings->filter('isListable', true);
	}

	public function title(): string
	{
		return $this->model->title()->toString();
	}
}
