<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelOpenButton::class)]
class ModelOpenButtonTest extends TestCase
{
	public function testButton(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new ModelOpenButton(model: $page);

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-open-view-button', $button->class);
		$this->assertSame('open', $button->icon);
		$this->assertNull($button->link);
		$this->assertSame('_blank', $button->target);
		$this->assertSame('Open', $button->title);
	}

	public function testRender(): void
	{
		$test      = $this;
		$this->app = new App();
		$this->app->impersonate('kirby', function () use ($test) {
			$page   = new Page(['slug' => 'test']);
			$button = new ModelOpenButton(model: $page);
			$render = $button->render();
			$props  = $render['props'];
			$test->assertSame('k-view-button', $render['component']);
			$test->assertSame('k-open-view-button', $props['class']);
			$test->assertSame('/test', $props['link']);
		});
	}

	public function testRenderNoButton(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new ModelOpenButton(model: $page);
		$this->assertNull($button->render());
	}
}
