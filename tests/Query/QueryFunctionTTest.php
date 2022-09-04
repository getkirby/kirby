<?php

namespace Kirby\Query;

use Kirby\Cms\App;

/**
 * @coversNothing
 */
class QueryFunctionTTest extends \PHPUnit\Framework\TestCase
{
	public function testTFunction()
	{
		$query = new Query('t("add")');
		$this->assertSame('Add', $query->resolve());

		$query = new Query('t("notfound", "fallback")');
		$this->assertSame('fallback', $query->resolve());

		$query = new Query('t("add", null, "de")');
		$this->assertSame('Hinzufügen', $query->resolve());
	}
}
