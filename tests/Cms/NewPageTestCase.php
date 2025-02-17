<?php

namespace Kirby\Cms;

class NewPageTestCase extends TestCase
{
	public function setUp(): void
	{
		$this->setUpTmp();
		$this->setUpSingleLanguage();

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
	}
}
