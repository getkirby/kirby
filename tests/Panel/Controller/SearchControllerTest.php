<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class TestSearchController extends SearchController
{
	public function load(): array
	{
		return [];
	}
}

#[CoversClass(SearchController::class)]
class SearchControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.SearchController';

	public function testFactory(): void
	{
		$controller = TestSearchController::factory();
		$this->assertInstanceOf(TestSearchController::class, $controller);
		$this->assertSame('', $controller->query);
		$this->assertSame(10, $controller->limit);
		$this->assertSame(1, $controller->page);
	}

	public function testFactoryWithQueryValues(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'request' => [
				'query' => [
					'query' => 'simpson',
					'limit' => 5,
					'page'  => 2
				]
			]
		]);

		$controller = TestSearchController::factory();
		$this->assertInstanceOf(TestSearchController::class, $controller);
		$this->assertSame('simpson', $controller->query);
		$this->assertSame(5, $controller->limit);
		$this->assertSame(2, $controller->page);
	}
}
