<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs';

	public function setUp(): void
	{
		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();

		// clear fake json requests
		$_GET = [];
	}
}
