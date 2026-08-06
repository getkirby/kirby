<?php

namespace Kirby\Guards;

use Kirby\Cms\App;
use Kirby\Cms\Model;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Validators for the input of `$page` actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PageValidators extends ModelValidators
{
	/**
	 * @var Page
	 */
	protected Model $model;

	/**
	 * Validates the input of the `changeNum` action
	 */
	protected function ensureToChangeNum(int|null $num = null): void
	{
		$this->validateNum($num);
	}

	/**
	 * Validates the input of the `changeSlug` action
	 */
	protected function ensureToChangeSlug(string $slug): void
	{
		$this->validateSlug($slug);
		$this->validateDuplicate($slug);
	}

	/**
	 * Validates the input of the `changeStatus` action
	 */
	protected function ensureToChangeStatus(
		string $status,
		int|null $position = null
	): void {
		$this->validateStatus($status);
	}

	/**
	 * Validates the input of the `changeStatusToListed` action
	 */
	protected function ensureToChangeStatusToListed(int $position): void
	{
		$this->validateNum($position);
	}

	/**
	 * Validates the input of the `changeTemplate` action
	 */
	protected function ensureToChangeTemplate(string $template): void
	{
		$this->validateTemplate($template);
	}

	/**
	 * Validates the input of the `changeTitle` action
	 */
	protected function ensureToChangeTitle(string $title): void
	{
		$this->validateTitle($title);
	}

	/**
	 * Validates the page that is about to be created
	 */
	protected function ensureToCreate(): void
	{
		$this->validateSlug($this->model->slug());
		$this->validateDoesNotExist();
		$this->validateDuplicate($this->model->slug(), strict: true);
	}

	/**
	 * Validates the input of the `delete` action
	 *
	 * @param bool $force Allows deleting a page that still has children
	 */
	protected function ensureToDelete(bool $force = false): void
	{
		if ($force === false) {
			$this->validateHasNoChildren();
		}
	}

	/**
	 * Validates the input of the `duplicate` action
	 */
	protected function ensureToDuplicate(string $slug, array $options = []): void
	{
		$this->validateSlug($slug);
	}

	/**
	 * Validates the input of the `move` action
	 */
	protected function ensureToMove(Site|Page $parent): void
	{
		$this->validateMoveTo($parent);
	}

	/**
	 * Validates that the page does not exist yet
	 */
	public function validateDoesNotExist(): void
	{
		if ($this->model->exists() === true) {
			throw new DuplicateException(
				key: 'page.draft.duplicate',
				data: ['slug' => $this->model->slug()]
			);
		}
	}

	/**
	 * Validates that no other page or draft with the same slug exists
	 *
	 * @param bool $strict If `true`, the page itself is not ignored,
	 *                     which is needed for pages that don't exist yet
	 */
	public function validateDuplicate(
		string $slug,
		Site|Page|null $parent = null,
		bool $strict = false
	): void {
		$parent ??= $this->model->parentModel();

		$child = $parent->children()->find($slug);
		$draft = $parent->drafts()->find($slug);

		if ($child !== null && ($strict === true || $child->is($this->model) === false)) {
			throw new DuplicateException(
				key: 'page.duplicate',
				data: ['slug' => $slug]
			);
		}

		if ($draft !== null && ($strict === true || $draft->is($this->model) === false)) {
			throw new DuplicateException(
				key: 'page.draft.duplicate',
				data: ['slug' => $slug]
			);
		}
	}

	/**
	 * Validates that the page has no children or drafts left,
	 * which would be deleted together with it
	 */
	public function validateHasNoChildren(): void
	{
		if (
			$this->model->hasChildren() === true ||
			$this->model->hasDrafts() === true
		) {
			throw new LogicException(key: 'page.delete.hasChildren');
		}
	}

	/**
	 * Validates if the page can be moved to the given parent
	 */
	public function validateMoveTo(Site|Page $parent): void
	{
		// the page cannot be moved into itself
		if (
			$parent instanceof Page === true &&
			(
				$this->model->is($parent) === true ||
				$this->model->isAncestorOf($parent) === true
			)
		) {
			throw new LogicException(key: 'page.move.ancestor');
		}

		$this->validateSlugProtectedPaths(slug: $this->model->slug(), parent: $parent);

		// check for duplicates
		if ($parent->childrenAndDrafts()->find($this->model->slug()) !== null) {
			throw new DuplicateException(
				key: 'page.move.duplicate',
				data: ['slug' => $this->model->slug()]
			);
		}

		$this->validateMoveToTemplate($parent);
	}

	/**
	 * Validates if the template of the page is accepted
	 * as a subpage template of the given parent
	 */
	public function validateMoveToTemplate(Site|Page $parent): void
	{
		$allowed = [];

		// collect all allowed subpage templates
		// from all pages sections in the blueprint
		// (only consider page sections that list pages
		// of the targeted new parent page)
		$sections = array_filter(
			$parent->blueprint()->sections(),
			fn ($section) =>
				$section->type() === 'pages' &&
				$section->parent()->is($parent)
		);

		// check if the parent has at least one pages section
		if ($sections === []) {
			throw new LogicException(
				key: 'page.move.noSections',
				data: ['parent' => $parent->id() ?? '/']
			);
		}

		// go through all allowed templates and
		// add the name to the allowlist
		foreach ($sections as $section) {
			foreach ($section->templates() as $template) {
				$allowed[] = $template;
			}
		}

		// check if the template of this page is allowed as subpage type
		// for the potential new parent
		if (
			$allowed !== [] &&
			in_array($this->model->intendedTemplate()->name(), $allowed, true) === false
		) {
			throw new PermissionException(
				key: 'page.move.template',
				data: [
					'template' => $this->model->intendedTemplate()->name(),
					'parent'   => $parent->id() ?? '/',
				]
			);
		}
	}

	/**
	 * Validates the sorting number
	 */
	public function validateNum(int|null $num = null): void
	{
		if ($num !== null && $num < 0) {
			$this->error(
				key: 'page.num.invalid'
			);
		}
	}

	/**
	 * Validates the length of the slug and that it does not
	 * collide with one of the protected root paths
	 */
	public function validateSlug(string $slug, Site|Page|null $parent = null): void
	{
		$this->validateSlugLength($slug);
		$this->validateSlugProtectedPaths(
			slug: $slug,
			parent: $parent
		);
	}

	/**
	 * Validates that the slug is not empty and does not exceed
	 * the maximum length to make sure that the directory name
	 * will be accepted by the filesystem
	 */
	public function validateSlugLength(string $slug): void
	{
		$slugLength = Str::length($slug);

		if ($slugLength === 0) {
			$this->error(
				key: 'page.slug.invalid'
			);
		}

		if ($slugsMaxlength = App::instance()->option('slugs.maxlength', 255)) {
			$maxlength = (int)$slugsMaxlength;

			if ($slugLength > $maxlength) {
				$this->error(
					key: 'page.slug.maxlength',
					data: ['length' => $maxlength]
				);
			}
		}
	}

	/**
	 * Validates that a top-level slug does not shadow one of the
	 * paths that Kirby reserves for the API, assets, media and Panel
	 */
	public function validateSlugProtectedPaths(string $slug, Site|Page|null $parent = null): void
	{
		$parent ??= $this->model->parent();

		if ($parent instanceof Page === true) {
			return;
		}

		$paths = A::map(
			['api', 'assets', 'media', 'panel'],
			fn ($url) => $this->model->kirby()->url($url, true)->path()->toString()
		);

		$index = array_search($slug, $paths, true);

		if ($index !== false) {
			$this->error(
				key: 'page.changeSlug.reserved',
				data: ['path' => $paths[$index]]
			);
		}
	}

	/**
	 * Validates that the status is defined in the blueprint
	 */
	public function validateStatus(string $status): void
	{
		if (isset($this->model->blueprint()->status()[$status]) === false) {
			$this->error(
				key: 'page.status.invalid'
			);
		}
	}

	/**
	 * Validates that the given template is a valid blueprint
	 * option for this page
	 */
	public function validateTemplate(string $template): void
	{
		$blueprints = $this->model->blueprints();

		if (
			count($blueprints) <= 1 ||
			in_array($template, array_column($blueprints, 'name'), true) === false
		) {
			$this->error(
				key: 'page.changeTemplate.invalid',
				data: ['slug' => $this->model->slug()]
			);
		}
	}

	/**
	 * Validates that the page title is not empty
	 */
	public function validateTitle(string $title): void
	{
		if (Str::length($title) === 0) {
			$this->error(
				key: 'page.changeTitle.empty'
			);
		}
	}
}
