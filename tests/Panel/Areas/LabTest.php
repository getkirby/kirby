<?php

namespace Kirby\Panel\Areas;

class LabTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testPlaygroundView(): void
	{
		$view  = $this->view('lab/components/buttons');
		$props = $view['props'];

		$this->assertSame('k-lab-playground-view', $view['component']);
		$this->assertTrue($props['compiler']);
	}

	public function testPlaygroundViewWithDisabledCompiler(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'vue' => [
						'compiler' => false
					]
				]
			]
		]);

		$this->install();
		$this->login();

		$view  = $this->view('lab/components/buttons');
		$props = $view['props'];

		$this->assertFalse($props['compiler']);
	}
}
