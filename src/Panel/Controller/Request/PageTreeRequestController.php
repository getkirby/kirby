<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Controller\RequestController;

/**
 * Returns children for the parent as entries
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageTreeRequestController extends RequestController
{
	protected Site|Page|null $parent;
	protected Page|null $move;

	public function __construct()
	{
		parent::__construct();

		$parent = $this->request->get('parent');
		$move   = $this->request->get('move');

		$this->parent = $parent ? Find::parent($parent) : null;
		$this->move   = $move ? Find::parent($move) : null;
	}

	/**
	 * Returns the properties to display the site or page
	 * as an entry in the page tree component
	 */
	public function entry(Site|Page $entry): array
	{
		$panel = $entry->panel();
		$id    = $entry->id() ?? '/';
		$uuid  = $entry->uuid()?->toString();
		$url   = $entry->url();
		$value = $uuid ?? $id;

		return [
			'children'    => $panel->url(true),
			'disabled'    => $this->move?->isMovableTo($entry) === false,
			'hasChildren' =>
				$entry->hasChildren() === true ||
				$entry->hasDrafts() === true,
			'icon'        => match (true) {
				$entry instanceof Site => 'home',
				default                => $panel->image()['icon'] ?? null
			},
			'id'          => $id,
			'open'        => false,
			'label'       => match (true) {
				$entry instanceof Site => $this->i18n('view.site'),
				default                => $entry->title()->value()
			},
			'url'         => $url,
			'uuid'        => $uuid,
			'value'       => $value
		];
	}

	public function load(): array
	{
		if ($this->parent === null) {
			return [
				$this->entry($this->site)
			];
		}

		return $this->parent
			->childrenAndDrafts()
			->filterBy('isListable', true)
			->values(
				fn ($child) => $this->entry($child)
			);
	}
}
