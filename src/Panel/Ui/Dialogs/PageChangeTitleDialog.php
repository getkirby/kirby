<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\PageRules;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageChangeTitleDialog extends FormDialog
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();
		$permissions   = $page->permissions();
		$select        = $this->request->get('select', 'title');

		parent::__construct(
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
	 * Builds the path prefix
	 */
	public function path(): string
	{
		$path = '/';

		if ($this->kirby->multilang() === true) {
			$url     = $this->kirby->url();
			$siteUrl = $this->kirby->site()->url();
			$path    = Str::after($siteUrl, $url) . $path;
		}

		if ($parent = $this->page->parent()) {
			$path .= $parent->uri() . '/';
		}

		return $path;
	}

	public function submit(): array|true
	{
		$title = trim($this->request->get('title', ''));
		$slug  = trim($this->request->get('slug', ''));

		// basic input validation before we move on
		PageRules::validateTitleLength($title);
		PageRules::validateSlugLength($slug);

		// nothing changed
		if (
			$this->page->title()->value() === $title &&
			$this->page->slug() === $slug
		) {
			return true;
		}

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
			if (Panel::referrer() === $oldUrl && $oldUrl !== $newUrl) {
				$response['redirect'] = $newUrl;
			}
		}

		return $response;
	}
}
