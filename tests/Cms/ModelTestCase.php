<?php

namespace Kirby\Cms;

use Kirby\Blueprint\Blueprint;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class ModelTestCase extends TestCase
{
	public function cleanUp(): void
	{
		// clean up after previous app instances
		App::destroy();

		// discard all cached blueprints
		Blueprint::$loaded = [];

		I18n::$locale       = null;
		I18n::$fallback     = 'en';
		I18n::$translations = [];
		Str::$language      = [];
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
