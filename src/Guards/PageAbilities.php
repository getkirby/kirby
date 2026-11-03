<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Page;

/**
 * Abilities for a `$page` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageAbilities extends ModelAbilities
{
	/**
	 * @var Page
	 */
	protected Model $model;

	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		parent::error(
			key: 'page.' . $key,
			data: [
				'slug' => $this->model->slug(),
				...$data
			],
			details: $details
		);
	}

	protected function ensureToChangeSlug(): void
	{
		if ($this->model->isHomePage() === true) {
			$this->error(key: 'changeSlug.homePage');
		}

		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'changeSlug.errorPage');
		}
	}

	protected function ensureToChangeStatus(): void
	{
		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'changeStatus.errorPage');
		}
	}

	protected function ensureToChangeStatusToDraft(): void
	{
		if ($this->model->isHomePage() === true) {
			$this->error(key: 'changeStatus.toDraft.homePage');
		}

		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'changeStatus.toDraft.errorPage');
		}
	}

	protected function ensureToChangeTemplate(): void
	{
		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'changeTemplate.errorPage');
		}

		if (count($this->model->blueprints()) <= 1) {
			$this->error(key: 'changeTemplate.invalid');
		}
	}

	protected function ensureToDelete(): void
	{
		if ($this->model->isHomePage() === true) {
			$this->error(key: 'delete.homePage');
		}

		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'delete.errorPage');
		}
	}

	protected function ensureToMove(): void
	{
		if ($this->model->isHomePage() === true) {
			$this->error(key: 'move.homePage');
		}

		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'move.errorPage');
		}
	}

	protected function ensureToPublish(): void
	{
		$this->ensureToChangeStatus();
	}

	protected function ensureToSort(): void
	{
		if ($this->model->isErrorPage() === true) {
			$this->error(key: 'sort.errorPage');
		}

		if ($this->model->isListed() === false) {
			$this->error(key: 'sort.unlisted');
		}

		if ($this->model->blueprint()->num() !== 'default') {
			$this->error(key: 'sort.mode');
		}
	}
}
