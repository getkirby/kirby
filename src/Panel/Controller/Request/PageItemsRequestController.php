<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\Page;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageItemsRequestController extends ModelItemsRequestController
{
	protected const ITEM_CLASS = PageItem::class;

	protected function model(string $id): Page|null
	{
		return $this->kirby->page($id);
	}
}
