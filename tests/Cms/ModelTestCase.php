<?php

namespace Kirby\Cms;

class ModelTestCase extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpTmp();
		$this->setUpSingleLanguage();

		$this->app->impersonate('kirby');
	}
}
