<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\File;
use Kirby\Panel\Ui\Item\FileItem;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FileItemsRequestController extends ModelItemsRequestController
{
	protected const ITEM_CLASS = FileItem::class;

	protected function model(string $id): File|null
	{
		return $this->kirby->file($id);
	}
}
