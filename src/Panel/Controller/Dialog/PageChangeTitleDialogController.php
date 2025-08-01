<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\PageRules;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Controls the Panel dialog for changing the title of a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageChangeTitleDialogController extends PageDialogController
{
	public function load(): Dialog
	{
		$permissions = $this->page->permissions();
		$select      = $this->request->get('select', 'title');

		return new FormDialog(
			fields: [
				'title' => Field::title([
					'required'  => true,
					'preselect' => $select === 'title',
					'disabled'  => $permissions->can('changeTitle') === false
				]),
				'slug' => Field::slug([
					'required'  => true,
					'preselect' => $select === 'slug',
					'path'      => $this->path(),
					'disabled'  => $permissions->can('changeSlug') === false,
					'wizard'    => [
						'text'  => I18n::translate('page.changeSlug.fromTitle'),
						'field' => 'title'
					]
				])
			],
			submitButton: I18n::translate('change'),
			value: [
				'title' => $this->page->title()->value(),
				'slug'  => $this->page->slug(),
			]
		);
	}

	/**
	 * Returns the path prefix
	 */
	public function path(): string
	{
		$path = '/';

		if ($this->kirby->multilang() === true) {
			$site = $this->kirby->site()->url();
			$url  = $this->kirby->url();
			$path = Str::after($site, $url) . '/';
		}

		if ($parent = $this->page->parent()) {
			$path .= $parent->uri() . '/';
		}

		return $path;
	}

	public function submit(): array|true
	{
		$title   = trim($this->request->get('title', ''));
		$slug    = trim($this->request->get('slug', ''));

		// basic input validation before we move on
		PageRules::validateTitleLength($title);
		PageRules::validateSlugLength($slug);

		// prepare the response
		$response = [
			'event' => []
		];

		// the page title changed
		if ($this->page->title()->value() !== $title) {
			$this->page = $this->page->changeTitle($title);
			$response['event'][] = 'page.changeTitle';
		}

		// the slug changed
		if ($this->page->slug() !== $slug) {
			$response['event'][] = 'page.changeSlug';

			$oldUrl     = $this->page->panel()->url(true);
			$this->page = $this->page->changeSlug($slug);
			$newUrl     = $this->page->panel()->url(true);

			// check for a necessary redirect after the slug has changed
			if (
				$this->kirby->panel()->referrer() === $oldUrl &&
				$oldUrl !== $newUrl
			) {
				$response['redirect'] = $newUrl;
			}
		}

		return $response;
	}
}
