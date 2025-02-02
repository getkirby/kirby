<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Page;
use Kirby\Cms\Url;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;
use Kirby\Uuid\Uuids;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageDuplicateDialog extends FormDialog
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
		parent::__construct(
			fields: $this->fields(),
			submitButton: I18n::translate('duplicate'),
			value: [
				'children' => false,
				'files'    => false,
				'slug'     => $this->slug(),
				'title'    => $this->title()
			]
		);
	}

	public function fields(): array
	{
		$hasChildren = $this->page->hasChildren();
		$hasFiles    = $this->page->hasFiles();
		$toggleWidth = '1/' . count(array_filter([$hasChildren, $hasFiles]));

		$fields = [
			'title' => Field::title([
				'required' => true
			]),
			'slug' => Field::slug([
				'required' => true,
				'path'     => $this->page->parent() ? '/' . $this->page->parent()->id() . '/' : '/',
				'wizard'   => [
					'text'  => I18n::translate('page.changeSlug.fromTitle'),
					'field' => 'title'
				]
			])
		];

		if ($hasFiles === true) {
			$fields['files'] = [
				'label' => I18n::translate('page.duplicate.files'),
				'type'  => 'toggle',
				'width' => $toggleWidth
			];
		}

		if ($hasChildren === true) {
			$fields['children'] = [
				'label' => I18n::translate('page.duplicate.pages'),
				'type'  => 'toggle',
				'width' => $toggleWidth
			];
		}

		return $fields;
	}

	public function props(): array
	{
		$parent = $this->page->parentModel();

		return [
			...parent::props(),
			'value' => [
				'move'   => $this->page->panel()->url(true),
				'parent' => match (Uuids::enabled()) {
					false   => $parent->id() ?? '/',
					default => $parent->uuid()->toString() ?? 'site://'
				}
			]
		];
	}

	public function slug(): string
	{
		$appendix = $this->slugAppendix();
		$counter  = $this->slugCounter();
		return $this->page->slug() . '-' . $appendix . $counter;
	}

	public function slugAppendix(): string
	{
		return Url::slug(I18n::translate('page.duplicate.appendix'));
	}

	public function slugCounter(): int|null
	{
		$siblings  = $this->page->parentModel()->childrenAndDrafts()->pluck('uid');

		// if the item to be duplicated already exists
		// add a suffix at the end of slug and title
		$slug = $this->page->slug() . '-' . $this->slugAppendix();

		if (in_array($slug, $siblings, true) === true) {
			$counter = 2;
			$newSlug = $slug . $counter;

			while (in_array($newSlug, $siblings, true) === true) {
				$newSlug = $slug . ++$counter;
			}

			return $counter;
		}

		return null;
	}

	public function submit(): array
	{
		$page = $this->page->duplicate($this->request->get('slug'), [
			'children' => (bool)$this->request->get('children'),
			'files'    => (bool)$this->request->get('files'),
			'title'    => (string)$this->request->get('title'),
		]);

		return [
			'event'    => 'page.duplicate',
			'redirect' => $page->panel()->url(true)
		];
	}

	public function title(): string
	{
		$appendix = I18n::translate('page.duplicate.appendix');
		$title    = $this->page->title() . ' ' . $appendix;

		if ($counter  = $this->slugCounter()) {
			$title .= ' ' . $counter;
		}

		return $title;
	}
}
