<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Url;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for duplicating a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageDuplicateDialogController extends PageDialogController
{
	public function fields(): array
	{
		$width = '1/' . count(array_filter([
			$this->page->hasChildren(),
			$this->page->hasFiles()
		]));

		$fields = [
			'title' => Field::title([
				'required' => true
			]),
			'slug' => Field::slug([
				'required' => true,
				'path'     => $this->path(),
				'wizard'   => [
					'text'  => I18n::translate('page.changeSlug.fromTitle'),
					'field' => 'title'
				]
			])
		];

		if ($this->page->hasFiles() === true) {
			$fields['files'] = [
				'label' => I18n::translate('page.duplicate.files'),
				'type'  => 'toggle',
				'width' => $width
			];
		}

		if ($this->page->hasChildren() === true) {
			$fields['children'] = [
				'label' => I18n::translate('page.duplicate.pages'),
				'type'  => 'toggle',
				'width' => $width
			];
		}

		return $fields;
	}

	public function load(): Dialog
	{
		return new FormDialog(
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

	public function path(): string
	{
		if ($parent = $this->page->parent()) {
			return '/' . $parent->id() . '/';
		}

		return '/';
	}

	public function slug(): string
	{
		return $this->page->slug() . '-' . $this->slugAppendix() . $this->suffixCount();
	}

	protected function slugAppendix(): string
	{
		return Url::slug(I18n::translate('page.duplicate.appendix'));
	}

	public function submit(): array
	{
		$newPage = $this->page->duplicate($this->request->get('slug'), [
			'children' => (bool)$this->request->get('children'),
			'files'    => (bool)$this->request->get('files'),
			'title'    => (string)$this->request->get('title'),
		]);

		return [
			'event'    => 'page.duplicate',
			'redirect' => $newPage->panel()->url(true)
		];
	}

	public function suffixCount(): int|null
	{
		// if the item to be duplicated already exists
		// add a suffix at the end of slug and title
		$slug     = $this->page->slug() . '-' . $this->slugAppendix();
		$siblings = $this->page->parentModel()->childrenAndDrafts()->pluck('uid');

		if (in_array($slug, $siblings, true) === true) {
			$count = 2;
			$slug  = $slug . $count;

			while (in_array($slug, $siblings, true) === true) {
				$slug = $slug . ++$count;
			}
		}

		return $count ?? null;
	}

	public function title(): string
	{
		$appendix = I18n::translate('page.duplicate.appendix');
		$title    = $this->page->title() . ' ' . $appendix;

		if ($count = $this->suffixCount()) {
			$title .= ' ' . $count;
		}

		return $title;
	}
}
