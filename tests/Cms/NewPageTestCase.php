<?php

namespace Kirby\Cms;

class NewPageTestCase extends TestCase
{
	public function cleanUp(): void
	{
		// clean up after previous app instances
		App::destroy();

		// discard all cached blueprints
		Blueprint::$loaded = [];
	}

	public function setUp(): void
	{
		$this->cleanUp();

		$this->setUpTmp();
		$this->setUpSingleLanguage();

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		$this->cleanUp();
		$this->tearDownTmp();
	}
}
