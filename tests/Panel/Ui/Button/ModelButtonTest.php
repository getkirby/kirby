<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelButton::class)]
class ModelButtonTest extends TestCase
{
	public function testModel(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new ModelButton(model: $page);
		$this->assertSame($page, $button->model());

		$button = new ModelButton();
		$this->assertNull($button->model());
	}

	public function testPropsWithStringTemplates(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new ModelButton(
			model: $page,
			dialog: '{{ page.slug }}/test',
			drawer: '{{ page.slug }}/test',
			icon: 'icon-{{ page.slug }}',
			link: '{{ page.slug }}/test',
			text: ['en' => 'Text {{ page.slug }}'],
			theme: 'theme-{{ page.slug }}',
			title: ['en' => 'Title {{ page.slug }}']
		);

		$props = $button->props();

		$this->assertSame('test/test', $props['dialog']);
		$this->assertSame('test/test', $props['drawer']);
		$this->assertSame('icon-test', $props['icon']);
		$this->assertSame('test/test', $props['link']);
		$this->assertSame('Text test', $props['text']);
		$this->assertSame('theme-test', $props['theme']);
		$this->assertSame('Title test', $props['title']);
	}

	public function testPropsWithoutModel(): void
	{
		$button = new ModelButton(
			dialog: '{{ page.slug }}/test',
			drawer: '{{ page.slug }}/test',
			icon: 'icon-{{ page.slug }}',
			link: '{{ page.slug }}/test',
			text: ['en' => 'Text {{ page.slug }}'],
			theme: 'theme-{{ page.slug }}',
			title: ['en' => 'Title {{ page.slug }}']
		);

		$props = $button->props();

		$this->assertSame('{{ page.slug }}/test', $props['dialog']);
		$this->assertSame('{{ page.slug }}/test', $props['drawer']);
		$this->assertSame('icon-{{ page.slug }}', $props['icon']);
		$this->assertSame('{{ page.slug }}/test', $props['link']);
		$this->assertSame('Text {{ page.slug }}', $props['text']);
		$this->assertSame('theme-{{ page.slug }}', $props['theme']);
		$this->assertSame('Title {{ page.slug }}', $props['title']);
	}
}
