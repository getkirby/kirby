<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\MemoryStorage;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTest';
}
