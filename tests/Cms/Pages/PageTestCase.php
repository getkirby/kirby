<?php

namespace Kirby\Cms;

class PageTestCase extends TestCase
{
	public $page = null;

	public function page(string|null $id = null)
	{
		return parent::page($id ?? $this->page);
	}
}
