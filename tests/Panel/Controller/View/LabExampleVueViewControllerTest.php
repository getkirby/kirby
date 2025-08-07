<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Http\Response;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Example;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabExampleVueViewController::class)]
class LabExampleVueViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LabExampleVueViewController';

	protected Example $example;

	public function setUp(): void
	{
		parent::setUp();
		$this->example = Category::factory('components')->example('buttons');
	}

	public function testFactory(): void
	{
		$controller = LabExampleVueViewController::factory('components', 'buttons');
		$this->assertInstanceOf(LabExampleVueViewController::class, $controller);
	}

	public function testLoad(): void
	{
		$controller = new LabExampleVueViewController($this->example);
		$response   = $controller->load();
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('export default {}', $response->body());
	}
}
